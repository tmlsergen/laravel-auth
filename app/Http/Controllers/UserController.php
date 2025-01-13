<?php

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Http\Requests\Web\User\UpdatePasswordRequest;
use App\Http\Requests\Web\User\UpdateRequest;
use App\Http\Requests\Web\User\Verify2FARequest;
use App\Http\Requests\Web\User\VerifyBackupRequest;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function me(): View
    {
        return view('user.me', ['user' => new UserResource(Auth::user())]);
    }

    public function update(UpdateRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->userService->updateProfile($validated, Auth::id());

            return back()->with('success', 'Profile updated successfully.');
        } catch (ServiceException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user'] = Auth::user();

        try {
            $this->userService->updatePassword($validated);

            return back()->with('password_status', 'Profile updated successfully.');
        } catch (ServiceException $e) {
            return back()->withErrors(['password_error' => $e->getMessage()]);
        }
    }

    public function sessions(): View
    {
        $user = Auth::user();

        return view('user.sessions', [
            'sessions' => SessionResource::collection($user->sessions()->paginate(10)),
        ]);
    }

    public function logoutFromDevice(string $sessionId): RedirectResponse
    {
        Session::getHandler()->destroy($sessionId);

        return redirect()->back()->with('status', 'Logged out from that device successfully.');
    }

    public function enable2fa(): View|RedirectResponse
    {
        try {
            $result = $this->userService->enable2fa(Auth::id());

            return view('2fa.enable', $result);
        } catch (ServiceException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function verify(): View
    {
        return view('2fa.verify');
    }

    public function verify2FA(Verify2FARequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->userService->verify2fa($validated['code'], Auth::id());

            return redirect()->route('user.me')->with('success', '2FA verified successfully.');
        } catch (ServiceException $e) {

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getBackupCodes(): View|RedirectResponse
    {
        try {
            $backUpCodes = $this->userService->getBackupCodes(Auth::id());

            return view('2fa.backup-codes', ['backUpCodes' => $backUpCodes]);
        } catch (ServiceException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function regenerateBackupCodes(): RedirectResponse
    {
        $userId = Auth::id();

        try {
            $this->userService->regenerateBackupCodes($userId);

            return back()->with('success', 'Backup codes regenerated successfully.');
        } catch (ServiceException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function backup(): View
    {
        return view('2fa.backup');
    }

    public function verifyBackup(VerifyBackupRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->userService->verifyBackup($validated['code'], Auth::id());

            return redirect()->route('user.me')->with('success', 'Your account is saved with backup codes and 2FA is disabled.');
        } catch (ServiceException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
