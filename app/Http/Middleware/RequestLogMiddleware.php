<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $requestData = $request->all();

        $user = $request->user();

        if (in_array('password', array_keys($requestData))) {
            $requestData['password'] = 'XXXX';
            $requestData['password_confirmation'] = 'XXXX';
            $request['current_password'] = 'XXXX';
        }

        if (in_array('_token', array_keys($requestData))) {
            unset($requestData['_token']);
        }

        $data = [
            'url' => $request->url(),
            'method' => $request->method(),
            'request' => $requestData,
            'ip' => $request->ip(),
            'http_version' => $_SERVER['SERVER_PROTOCOL'],
            'headers' => [
                'user-agent' => $request->header('user-agent'),
                'accept-language' => $request->header('accept-language'),
                'referer' => $request->header('referer'),
                'origin' => $request->header('origin'),
            ],
            'userId' => $user?->id,
        ];

        Log::channel('request')->info('Incoming Request', $data);

        return $response;
    }
}
