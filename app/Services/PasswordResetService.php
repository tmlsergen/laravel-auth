<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RateLimit;
use App\Exceptions\JwtServiceException;
use App\Exceptions\NotFoundException;
use App\Exceptions\RepositoryException;
use App\Exceptions\ServiceException;
use App\Repositories\PasswordHistoryRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

readonly class PasswordResetService
{
    public function __construct(
        private UserRepository $userRepository,
        private JwtTokenService $jwtTokenService,
        private PasswordHistoryRepository $passwordHistoryRepository,
    ) {
    }

    /**
     * @throws ServiceException
     */
    public function sendConfirmationMail(string $email): string
    {
        $key = $this->getPasswordResetCacheKey($email);

        try {
            if (RateLimiter::tooManyAttempts($key, RateLimit::MAX_ATTEMPTS->value)) {
                $seconds = RateLimiter::availableIn($key);
                throw new ServiceException("Too many requests. Please try again in {$seconds} seconds.");
            }

            $this->userRepository->findByEmail($email);
            RateLimiter::hit($key, RateLimit::DECAY_MINUTES->value * 60);

            $this->jwtTokenService->setKey(config('app.jwt_secret_password'));
            $token = $this->jwtTokenService->encode([
                'email' => $email,
            ]);

            $url = route('password.reset', ['token' => $token]);
            MailService::sendPasswordReset($email, $url);

            return 'Password reset link sent';
        } catch (NotFoundException $e) {
            Log::error('PasswordResetService@sendConfirmationMail', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);

            return 'Password reset link sent.';
        } catch (RepositoryException|JwtServiceException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     */
    public function resetPassword(array $params): void
    {
        try {
            $this->jwtTokenService->setKey(config('app.jwt_secret_password'));
            $claim = $this->jwtTokenService->decode($params['token']);

            $email = $claim->data->email;
            $user = $this->userRepository->findByEmail($email);

            $user->passwordHistories()->orderBy('created_at', 'desc')->limit(3)
                ->get()
                ->each(function ($history) use ($params) {
                    if (Hash::check($params['password'], $history->password)) {
                        throw new ServiceException('Password cannot be the same as the last 3 passwords');
                    }
                });

            $this->userRepository->updateById([
                'password' => Hash::make($params['password']),
                'password_changed_at' => now(),
            ], $user->id);

            $this->passwordHistoryRepository->create([
                'user_id' => $user->id,
                'password' => $user->password,
            ]);

            RateLimiter::clear($this->getPasswordResetCacheKey($email));
        } catch (NotFoundException) {
            throw new ServiceException('Invalid token');
        } catch (RepositoryException|JwtServiceException $exception) {
            throw new ServiceException($exception->getMessage());
        }
    }

    private function getPasswordResetCacheKey(string $email): string
    {
        return 'password_reset.'.strtolower($email).'.'.request()->ip();
    }
}
