<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\JwtServiceException;
use App\Services\JwtTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class CheckPasswordTokenMiddleware
{
    public function __construct(private JwtTokenService $jwtTokenService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->token;
        if (! $token) {
            return redirect()->route('password.reset')->with('error', 'Invalid token');
        }

        try {
            $this->jwtTokenService->setKey(config('app.jwt_secret_password'));
            $claim = $this->jwtTokenService->decode($token);

            $request->attributes->add(['claim' => $claim]);

            return $next($request);
        } catch (JwtServiceException $exception) {
            return redirect()->route('password.request')->withErrors(['error' => $exception->getMessage()]);
        }
    }
}
