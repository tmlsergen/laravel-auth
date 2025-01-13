<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Http\Requests\Web\PasswordReset\StoreRequest;
use App\Http\Requests\Web\PasswordReset\UpdateRequest;
use App\Services\PasswordResetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PasswordResetController extends Controller
{
    public function __construct(private readonly PasswordResetService $passwordResetService)
    {
    }

    public function create(): View
    {
        return view('password-reset.index');
    }

    public function store(StoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        try {
            $message = $this->passwordResetService->sendConfirmationMail($validated['email']);

            return redirect()->back()->with('status', $message);
        } catch (ServiceException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit(string $token): View
    {
        return view('password-reset.reset', ['token' => $token]);
    }

    public function update(UpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->passwordResetService->resetPassword($validated);

            return redirect()->route('login')->with('status', 'Password reset successfully');
        } catch (ServiceException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
