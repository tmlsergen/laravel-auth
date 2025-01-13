<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            \App\Http\Middleware\RequestLogMiddleware::class,
            \App\Http\Middleware\SecureHeaders::class,
        ]);

        $middleware->alias([
            'password.expire' => \App\Http\Middleware\Web\CheckPasswordExpireMiddleware::class,
            'api.auth' => \App\Http\Middleware\Api\ApiAuthMiddleware::class,
            '2fa' => \App\Http\Middleware\TwoFAMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/oauth/token',
            '/oauth/token/revoke',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
