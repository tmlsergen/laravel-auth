<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\JwtServiceException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServiceException;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Repositories\PasswordHistoryRepository;
use App\Repositories\UserRepository;
use App\Services\JwtTokenService;
use App\Services\PasswordResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Tests\TestCase;

final class PasswordResetServiceTest extends TestCase
{
    use RefreshDatabase;

    private PasswordResetService $passwordResetService;
    private UserRepository $userRepository;
    private JwtTokenService $jwtTokenService;
    private PasswordHistoryRepository $passwordHistoryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->jwtTokenService = Mockery::mock(JwtTokenService::class);
        $this->passwordHistoryRepository = Mockery::mock(PasswordHistoryRepository::class);

        $this->passwordResetService = new PasswordResetService(
            $this->userRepository,
            $this->jwtTokenService,
            $this->passwordHistoryRepository
        );
    }

    public function test_send_confirmation_mail_successful(): void
    {
        // Arrange
        $email = 'test@example.com';
        $token = 'jwt-token';
        $user = new User();
        $user->forceFill(['email' => $email]);

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(false);
        RateLimiter::shouldReceive('hit')->once();

        $this->userRepository
            ->expects('findByEmail')
            ->with($email)
            ->andReturn($user);

        $this->jwtTokenService
            ->expects('setKey')
            ->with(config('app.jwt_secret_password'))
            ->andReturnSelf();

        $this->jwtTokenService
            ->expects('encode')
            ->with(['email' => $email])
            ->andReturn($token);

        // Act
        $result = $this->passwordResetService->sendConfirmationMail($email);

        // Assert
        $this->assertEquals('Password reset link sent', $result);
    }

    public function test_send_confirmation_mail_with_too_many_attempts(): void
    {
        // Arrange
        $email = 'test@example.com';

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->andReturn(300);

        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Too many requests. Please try again in 300 seconds.');

        // Act
        $this->passwordResetService->sendConfirmationMail($email);
    }

    public function test_send_confirmation_mail_with_invalid_email(): void
    {
        // Arrange
        $email = 'invalid@example.com';

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(false);

        $this->userRepository
            ->expects('findByEmail')
            ->with($email)
            ->andThrow(new NotFoundException('User not found'));

        // Act
        $result = $this->passwordResetService->sendConfirmationMail($email);

        // Assert
        $this->assertEquals('Password reset link sent.', $result);
    }

    public function test_reset_password_successful(): void
    {
        // Arrange
        $email = 'test@example.com';
        $newPassword = 'new-password123';
        $token = 'valid-token';

        $user = new User();
        $user->forceFill([
            'id' => 1,
            'email' => $email,
            'password' => Hash::make('old-password'),
        ]);

        $claim = (object)['data' => (object)['email' => $email]];

        $this->jwtTokenService
            ->expects('setKey')
            ->with(config('app.jwt_secret_password'))
            ->andReturnSelf();

        $this->jwtTokenService
            ->expects('decode')
            ->with($token)
            ->andReturn($claim);

        $this->userRepository
            ->expects('findByEmail')
            ->with($email)
            ->andReturn($user);

        $this->userRepository
            ->expects('updateById')
            ->withArgs(function ($data, $id) use ($user, $newPassword) {
                return Hash::check($newPassword, $data['password']) &&
                    $data['password_changed_at'] instanceof \Carbon\Carbon &&
                    $id === $user->id;
            })
            ->andReturn(1);

        $this->passwordHistoryRepository
            ->expects('create')
            ->withArgs(function ($data) use ($user) {
                return $data['user_id'] === $user->id &&
                    $data['password'] === $user->password;
            })
            ->andReturn(new PasswordHistory());

        RateLimiter::shouldReceive('clear')->once();

        // Act
        $this->passwordResetService->resetPassword([
            'token' => $token,
            'password' => $newPassword,
        ]);

        // Assert
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_reset_password_with_invalid_token(): void
    {
        // Arrange
        $token = 'invalid-token';

        $this->jwtTokenService
            ->expects('setKey')
            ->with(config('app.jwt_secret_password'))
            ->andReturnSelf();

        $this->jwtTokenService
            ->expects('decode')
            ->with($token)
            ->andThrow(new JwtServiceException('Invalid token'));

        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Invalid token');

        // Act
        $this->passwordResetService->resetPassword([
            'token' => $token,
            'password' => 'new-password123',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
