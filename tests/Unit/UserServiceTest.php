<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Role;
use App\Exceptions\ServiceException;
use App\Models\User;
use App\Repositories\PasswordHistoryRepository;
use App\Repositories\UserRepository;
use App\Services\TwoFAService;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

final class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private PasswordHistoryRepository $passwordHistoryRepository;
    private TwoFAService $twoFAService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->passwordHistoryRepository = Mockery::mock(PasswordHistoryRepository::class);
        $this->twoFAService = Mockery::mock(TwoFAService::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->passwordHistoryRepository,
            $this->twoFAService
        );

        Storage::fake('s3');
    }

    public function test_update_password_successful(): void
    {
        // Arrange
        $currentPassword = 'current-password';
        $newPassword = 'new-password123';

        $user = new User();
        $user->forceFill([
            'id' => 1,
            'password' => Hash::make($currentPassword),
        ]);

        $this->passwordHistoryRepository
            ->shouldReceive('create')
            ->withArgs(function ($data) use ($user, $newPassword) {
                return $data['user_id'] === $user->id &&
                    Hash::check($newPassword, $data['password']);
            })
            ->once();

        $this->userRepository
            ->shouldReceive('updateById')
            ->withArgs(function ($data, $id) use ($user, $newPassword) {
                return Hash::check($newPassword, $data['password']) &&
                    $data['password_changed_at'] instanceof \Carbon\Carbon &&
                    $id === $user->id;
            })
            ->once();

        // Act
        $this->userService->updatePassword([
            'user' => $user,
            'current_password' => $currentPassword,
            'password' => $newPassword,
        ]);

        // Assert
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_update_password_with_incorrect_current_password(): void
    {
        // Arrange
        $user = new User();
        $user->forceFill([
            'password' => Hash::make('actual-password'),
        ]);

        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Your current password is incorrect.');

        // Act
        $this->userService->updatePassword([
            'user' => $user,
            'current_password' => 'wrong-password',
            'password' => 'new-password',
        ]);
    }

    public function test_enable_2fa_successful(): void
    {
        // Arrange
        $userId = 1;
        $email = 'test@example.com';
        $qrFilePath = '2fa/qr/1.svg';
        $secret = 'secret-key';
        $codes = ['code1', 'code2'];
        $codeFilePath = '2fa/recovery/1_recovery.json';
        $qrContent = 'qr-content';

        $user = new User();
        $user->forceFill([
            'id' => $userId,
            'email' => $email,
            'two_factor_enabled' => false,
        ]);

        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn($user);

        $this->twoFAService
            ->shouldReceive('enable2fa')
            ->with($email, $userId)
            ->andReturn([$qrFilePath, $secret, $codes, $codeFilePath]);

        $this->userRepository
            ->shouldReceive('updateById')
            ->withArgs(function ($data, $id) use ($userId, $secret, $qrFilePath, $codeFilePath) {
                return $data['two_factor_enabled'] === true &&
                    $data['two_factor_secret'] === $secret &&
                    $data['two_factor_qr_path'] === $qrFilePath &&
                    $data['two_factor_recovery_path'] === $codeFilePath &&
                    $id === $userId;
            });

        Storage::disk('s3')->put($qrFilePath, $qrContent);

        // Act
        $result = $this->userService->enable2fa($userId);

        // Assert
        $this->assertEquals([
            'qr' => $qrContent,
            'backUpCodes' => $codes,
        ], $result);
    }

    public function test_verify_2fa_successful(): void
    {
        // Arrange
        $userId = 1;
        $code = '123456';
        $secret = 'secret-key';

        $user = new User();
        $user->forceFill([
            'id' => $userId,
            'two_factor_secret' => $secret,
            'role' => Role::USER->value,
        ]);

        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn($user);

        $this->twoFAService
            ->shouldReceive('verify2fa')
            ->with($code, $secret)
            ->once();

        // Act
        $this->userService->verify2FA($code, $userId);

        // Assert
        $this->assertTrue(session()->has('2fa_passed'));
    }

    public function test_get_backup_codes_successful(): void
    {
        // Arrange
        $userId = 1;
        $recoveryPath = '2fa/recovery/1_recovery.json';
        $codes = ['code1', 'code2'];

        $user = new User();
        $user->forceFill([
            'id' => $userId,
            'two_factor_enabled' => true,
            'two_factor_recovery_path' => $recoveryPath,
        ]);

        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn($user);

        $this->twoFAService
            ->shouldReceive('getBackupCodes')
            ->with($recoveryPath)
            ->andReturn(json_encode($codes));

        // Act
        $result = $this->userService->getBackupCodes($userId);

        // Assert
        $this->assertEquals($codes, $result);
    }

    public function test_verify_backup_successful(): void
    {
        // Arrange
        $userId = 1;
        $code = 'valid-backup-code';
        $recoveryPath = '2fa/recovery/1_recovery.json';
        $backupCodes = ['valid-backup-code', 'another-code', 'unused-code'];

        $user = new User();
        $user->forceFill([
            'id' => $userId,
            'two_factor_enabled' => true,
            'two_factor_secret' => 'secret-key',
            'two_factor_qr_path' => 'path/to/qr.svg',
            'two_factor_recovery_path' => $recoveryPath,
        ]);

        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn($user);

        $this->twoFAService
            ->shouldReceive('getBackupCodes')
            ->with($recoveryPath)
            ->andReturn(json_encode($backupCodes));

        $this->userRepository
            ->shouldReceive('updateById')
            ->withArgs(function ($data, $id) use ($userId) {
                return $id === $userId &&
                    $data['two_factor_enabled'] === false &&
                    $data['two_factor_secret'] === '' &&
                    $data['two_factor_qr_path'] === '' &&
                    $data['two_factor_recovery_path'] === '';
            })
            ->once();

        // Act
        $this->userService->verifyBackup($code, $userId);

        // Assert
        $this->assertTrue(true); // No exception thrown means test passed
    }

    public function test_verify_backup_with_invalid_code(): void
    {
        // Arrange
        $userId = 1;
        $code = 'invalid-backup-code';
        $recoveryPath = '2fa/recovery/1_recovery.json';
        $backupCodes = ['valid-code-1', 'valid-code-2', 'valid-code-3'];

        $user = new User();
        $user->forceFill([
            'id' => $userId,
            'two_factor_enabled' => true,
            'two_factor_recovery_path' => $recoveryPath,
        ]);

        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn($user);

        $this->twoFAService
            ->shouldReceive('getBackupCodes')
            ->with($recoveryPath)
            ->andReturn(json_encode($backupCodes));

        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Invalid backup code.');

        // Act
        $this->userService->verifyBackup($code, $userId);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
