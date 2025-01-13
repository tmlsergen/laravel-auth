<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Http\Requests\Web\Password\UpdateRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function expired(): View
    {
        return view('password.expired');
    }

    public function updateExpiredPassword(UpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['user'] = Auth::user();

        try {
            $this->userService->updatePassword($validated);

            return redirect()->route('dashboard')->with('status', 'Your password has been updated');
        } catch (ServiceException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
