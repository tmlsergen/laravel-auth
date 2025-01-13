<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFAMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->two_factor_enabled
            && ! $request->session()->has('2fa_passed')
            && $request->route()->getName() !== '2fa.verify'
            && $request->route()->getName() !== '2fa.verify.post'
            && $request->route()->getName() !== '2fa.backup'
            && $request->route()->getName() !== '2fa.backup.post'
        ) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
