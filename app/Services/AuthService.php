<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RateLimit;
use App\Exceptions\NotFoundException;
use App\Exceptions\RepositoryException;
use App\Exceptions\ServiceException;
use App\Models\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

readonly class AuthService
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    /**
     * @throws ServiceException
     */
    public function login(string $email, string $password, bool $remember, string $guard): User
    {
        $key = 'login.'.strtolower($email).'.'.request()->ip();

        try {
            $user = $this->userRepository->findByEmail($email);

            if ($user->locked_until && now()->lt($user->locked_until)) {
                $remainingMinutes = (int) now()->diffInMinutes($user->locked_until);
                throw new ServiceException("Account is locked. Please try again in {$remainingMinutes} minutes.");
            }

            if (RateLimiter::tooManyAttempts($key, RateLimit::MAX_ATTEMPTS->value)) {
                $seconds = RateLimiter::availableIn($key);
                throw new ServiceException("Too many login attempts. Please try again in {$seconds} seconds.");
            }

            if (! password_verify($password, $user->password)) {
                RateLimiter::hit($key, 60 * RateLimit::DECAY_MINUTES->value);

                $loginAttempts = $user->failed_login_attempts + 1;

                if ($loginAttempts >= RateLimit::MAX_ATTEMPTS->value) {
                    $this->userRepository->updateById([
                        'failed_login_attempts' => 0,
                        'locked_until' => Carbon::now()->addMinutes(RateLimit::LOCKOUT_MINUTES->value),
                    ], $user->id);

                    RateLimiter::clear($key);
                    throw new ServiceException('Account is locked for '.RateLimit::LOCKOUT_MINUTES->value.' minutes due to too many failed attempts.');
                }

                $this->userRepository->updateById([
                    'failed_login_attempts' => $loginAttempts,
                ], $user->id);

                throw new ServiceException('Invalid credentials');
            }

            if ($user->password_changed_at->diffInMonths(now()) >= 6) {
                throw new ServiceException('Your password has expired.');
            }

            $this->userRepository->updateById([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
            ], $user->id);

            RateLimiter::clear($key);
            Auth::guard($guard)->login($user, $remember);
            //            Auth::logoutOtherDevices($password); // Uncomment this line if you want to logout other devices

            return $user;

        } catch (NotFoundException $e) {
            RateLimiter::hit($key, 60 * RateLimit::DECAY_MINUTES->value);

            throw new ServiceException('Invalid credentials');
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * Logout the user
     */
    public function logout(): void
    {
        Auth::guard()->logout();
    }

    /**
     * @throws ServiceException
     */
    public function register(array $params): void
    {
        try {
            $this->userRepository->create([
                'name' => $params['name'],
                'email' => $params['email'],
                'password' => Hash::make($params['password']),
            ]);
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }
}
