<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Exceptions\NotFoundException;
use App\Exceptions\RepositoryException;
use App\Exceptions\ServiceException;
use App\Exceptions\TwoFAServiceException;
use App\Models\User;
use App\Repositories\PasswordHistoryRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHistoryRepository $passwordHistoryRepository,
        private TwoFAService $twoFAService
    ) {
    }

    /**
     * @throws ServiceException
     */
    public function updatePassword(array $params): void
    {
        $user = $params['user'];

        if (! Hash::check($params['current_password'], $user->password)) {
            throw new ServiceException('Your current password is incorrect.');
        }

        if (Hash::check($params['password'], $user->password)) {
            throw new ServiceException('Your new password cannot be the same as your current password.');
        }

        $user->passwordHistories()->orderBy('created_at', 'desc')->limit(3)
            ->each(function ($passwordHistory) use ($params) {
                if (Hash::check($params['password'], $passwordHistory->password)) {
                    throw new ServiceException('Your password has been used before. Please try a different password.');
                }
            });

        try {
            $this->passwordHistoryRepository->create([
                'user_id' => $user->id,
                'password' => Hash::make($params['password']),
            ]);

            $this->userRepository->updateById([
                'password' => Hash::make($params['password']),
                'password_changed_at' => now(),
            ], $user->id);
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     */
    public function updateProfile(array $params, int $userId): void
    {
        try {
            $this->userRepository->updateById([
                'name' => $params['name'],
            ], $userId);
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     */
    public function getUserById(int $userId): User
    {
        try {
            /** @var User $user */
            $user = $this->userRepository->findById($userId);

            return $user;
        } catch (NotFoundException) {
            throw new ServiceException('User not found.');
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     */
    public function enable2fa(int $userId): array
    {
        try {
            $user = $this->getUserById($userId);
            if ($user->two_factor_enabled) {
                throw new ServiceException('2FA is already enabled.');
            }

            [$qrFilePath, $secret, $codes, $codeFilePath] = $this->twoFAService->enable2fa($user->email, $userId);

            $this->userRepository->updateById([
                'two_factor_enabled' => true,
                'two_factor_secret' => $secret,
                'two_factor_qr_path' => $qrFilePath,
                'two_factor_recovery_path' => $codeFilePath,
            ], $userId);

            return [
                'qr' => Storage::disk('s3')->get($qrFilePath),
                'backUpCodes' => $codes,
            ];
        } catch (RepositoryException|TwoFAServiceException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     */
    public function verify2FA(string $code, int $userId): void
    {
        try {
            $user = $this->getUserById($userId);

            if ($user->role === Role::ADMIN->value) {
                session()->put('2fa_passed', true);

                return;
            }

            $this->twoFAService->verify2fa($code, $user->two_factor_secret);

            session()->put('2fa_passed', true);
        } catch (TwoFAServiceException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @return mixed
     *
     * @throws ServiceException
     */
    public function getBackupCodes(int $userId): array
    {
        try {
            $user = $this->getUserById($userId);
            if (! $user->two_factor_recovery_path || ! $user->two_factor_enabled) {
                throw new ServiceException('No backup codes found.');
            }

            $jsonCodes = $this->twoFAService->getBackupCodes($user->two_factor_recovery_path);

            return json_decode($jsonCodes);
        } catch (TwoFAServiceException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     */
    public function regenerateBackupCodes(int $userId): array
    {
        try {
            $user = $this->getUserById($userId);
            if (! $user->two_factor_enabled) {
                throw new ServiceException('No backup codes found.');
            }

            [$codes, $filePath] = $this->twoFAService->generateBackupCodes($user->id);

            $this->userRepository->updateById([
                'two_factor_recovery_path' => $filePath,
            ], $userId);

            return $codes;
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     */
    public function verifyBackup(string $code, int $userId): void
    {
        $codes = $this->getBackupCodes($userId);
        if (! in_array($code, $codes)) {
            throw new ServiceException('Invalid backup code.');
        }

        try {
            $this->userRepository->updateById([
                'two_factor_recovery_path' => '',
                'two_factor_enabled' => false,
                'two_factor_secret' => '',
                'two_factor_qr_path' => '',
            ], $userId);
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }
}
