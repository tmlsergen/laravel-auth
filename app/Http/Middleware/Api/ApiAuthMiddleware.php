<?php

namespace App\Http\Middleware\Api;

use App\Enums\OAuthScope;
use App\Exceptions\JwtServiceException;
use App\Exceptions\ServiceException;
use App\Services\JwtTokenService;
use App\Services\OAuthService;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class ApiAuthMiddleware
{
    use ApiResponse;

    public function __construct(
        private JwtTokenService $jwtTokenService,
        private OAuthService $oAuthService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->errorResponse('Unauthorized', 401);
        }

        try {
            $file = storage_path('oauth-public.key');
            $claim = $this->jwtTokenService->decode($token, $file);
            $token = $claim->data->data->user->token;
            $this->oAuthService->checkToken($token);

            $find = 0;
            foreach ($claim->data->data->scopes as $scope) {
                if (in_array($request->route()->getName(), OAuthScope::permissions($scope))) {
                    $find = 0;
                    break;
                }

                $find++;
            }
            if ($find > 0) {
                return $this->errorResponse('Unauthorized', 401);
            }

            $request->attributes->add(['claim' => $claim]);

            return $next($request);
        } catch (ServiceException|JwtServiceException) {
            return $this->errorResponse('Unauthorized', 401);
        }
    }
}
