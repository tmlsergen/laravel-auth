<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AuthGuard;
use App\Exceptions\ServiceException;
use App\Http\Requests\Web\Auth\LoginRequest;
use App\Http\Requests\Web\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function loginPage(): View
    {
        return view('login.index');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $remember = $request->has('remember');
        try {
            $this->authService->login($validated['email'], $validated['password'], $remember, AuthGuard::WEB->value);

            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        } catch (ServiceException $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function registerPage(): View
    {
        return view('register.index');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->authService->register($validated);

            return redirect()->route('login')->with('status', 'Registration successful. Please login to continue.');
        } catch (ServiceException $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
}
