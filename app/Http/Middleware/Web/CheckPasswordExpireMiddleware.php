<?php

declare(strict_types=1);

namespace App\Http\Middleware\Web;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class CheckPasswordExpireMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! in_array($request->route()->getName(), ['password.expired_update', 'password.expired'])
            && $user->password_changed_at->diffInDays(now()) > config('auth.password_expire_days')) {
            return redirect()->route('password.expired');
        }

        return $next($request);
    }
}
