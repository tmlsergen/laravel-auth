<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\RateLimit;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServiceException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Tests\TestCase;

final class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->authService = new AuthService($this->userRepository);
    }

    public function test_login_successful(): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = Hash::make($password);

        $userData = [
            'id' => 1,
            'email' => $email,
            'password' => $hashedPassword,
            'password_changed_at' => now(),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ];
        
        $user = new User();

        $user->forceFill($userData);
        $this->userRepository
            ->expects('findByEmail')
            ->with($email)
            ->andReturn($user);

        $this->userRepository
            ->expects('updateById')
            ->withArgs(function ($data, $id) use ($user) {
                return $data['failed_login_attempts'] === 0 &&
                    $data['locked_until'] === null &&
                    $data['last_login_at'] instanceof Carbon &&
                    $id === $user->id;
            })
            ->andReturn(1);

        Auth::shouldReceive('guard')
            ->once()
            ->with('web')
            ->andReturn(Mockery::mock(['login' => true]));

        // Act
        $result = $this->authService->login($email, $password, false, 'web');

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->email);
    }

    public function test_login_with_invalid_credentials(): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'wrong_password';


        $userData = [
            'id' => 1,
            'email' => $email,
            'password' => Hash::make('correct_password'),
            'password_changed_at' => now(),
            'failed_login_attempts' => 0,
        ];
        $user = new User();
        $user->forceFill($userData);

        $this->userRepository
            ->expects('findByEmail')
            ->with($email)
            ->andReturn($user);

        $this->userRepository
            ->expects('updateById')
            ->withArgs(function ($data, $id) use ($user) {
                return $data['failed_login_attempts'] === 1 &&
                    $id === $user->id;
            })
            ->andReturn(1);

        RateLimiter::shouldReceive('hit')->once();
        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(false);

        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Invalid credentials');

        // Act
        $this->authService->login($email, $password, false, 'web');
    }

    public function test_login_with_locked_account(): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        $lockedUntil = now()->addMinutes(30);

        $user = new User([
            'email' => $email,
            'password' => Hash::make($password),
            'locked_until' => $lockedUntil,
            'password_changed_at' => now(),
        ]);

        $this->userRepository
            ->expects('findByEmail')
            ->with($email)
            ->andReturn($user);

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(false);

        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Account is locked. Please try again in 29 minutes.');

        // Act
        $this->authService->login($email, $password, false, 'web');
    }

    public function test_login_with_too_many_attempts(): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';

        $user = new User([
            'email' => $email,
            'password' => Hash::make($password),
            'password_changed_at' => now(),
        ]);

        $this->userRepository
            ->expects('findByEmail')
            ->with($email)
            ->andReturn($user);

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->andReturn(300);

        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Too many login attempts. Please try again in 300 seconds.');

        // Act
        $this->authService->login($email, $password, false, 'web');
    }

    public function test_register_successful(): void
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $this->userRepository
            ->expects('create')
            ->with(Mockery::on(function ($data) use ($userData) {
                return $data['name'] === $userData['name'] &&
                    $data['email'] === $userData['email'] &&
                    Hash::check($userData['password'], $data['password']);
            }))
            ->andReturn(new User($userData));

        // Act
        $this->authService->register($userData);

        // Assert
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_logout_successful(): void
    {
        // Arrange
        Auth::shouldReceive('guard')
            ->once()
            ->andReturn(Mockery::mock(['logout' => true]));

        // Act
        $this->authService->logout();

        // Assert
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
