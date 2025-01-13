<?php

namespace App\Services\OAuth;

use App\Exceptions\JwtServiceException;
use App\Exceptions\NotFoundException;
use App\Exceptions\OAuthServiceException;
use App\Exceptions\RepositoryException;
use App\Models\User;
use App\Repositories\OAuthAccessTokenRepository;
use App\Repositories\UserRepository;
use App\Services\JwtTokenService;
use Illuminate\Support\Facades\Cache;
use Random\RandomException;

class AuthorizationCodeGrantService implements GrantServiceInterface
{
    public function __construct(
        private OAuthAccessTokenRepository $oauthAccessTokenRepository,
        private UserRepository $userRepository,
        private JwtTokenService $jwtTokenService
    ) {
    }

    /**
     * @throws OAuthServiceException
     */
    public function generateTokenResult(array $params): array
    {
        $session = Cache::get($params['code'], false);
        if (! $session) {
            throw new OAuthServiceException('Invalid code');
        }

        if ($session['client_id'] !== $params['client_id']) {
            throw new OAuthServiceException('Invalid client id');
        }

        try {
            /** @var User $user */
            $user = $this->userRepository->findById($session['user_id']);
            $token = bin2hex(random_bytes(4).$user->id);

            $accessToken = $this->generateAccessToken($user, $token, $session['scopes']);
            $refreshToken = $this->generateRefreshToken($user, $token, $session['scopes']);
            $expires = config('app.jwt_expiration');

            $this->oauthAccessTokenRepository->create([
                'access_token' => $token,
                'user_id' => $user->id,
                'client_id' => $session['client_id'],
                'scope' => implode(' ', $session['scopes']),
                'expires' => now()->addSeconds((int) $expires),
            ]);

            Cache::forget($params['code']);

            return [
                'token_type' => 'Bearer',
                'expires_in' => now()->addSeconds((int) $expires),
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ];
        } catch (NotFoundException) {
            throw new OAuthServiceException('Invalid credentials');
        } catch (RandomException) {
            throw new OAuthServiceException('Internal server error');
        } catch (RepositoryException $exception) {
            throw new OAuthServiceException($exception->getMessage());
        }
    }

    /**
     * @throws OAuthServiceException
     */
    private function generateAccessToken(User $user, string $token, array $scopes): string
    {
        $payload = $this->jwtTokenService->generatePayload([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token,
            ],
            'scopes' => $scopes,
        ], config('app.jwt_expiration'));

        try {
            return $this->jwtTokenService->encode($payload, storage_path('oauth-private.key'));
        } catch (JwtServiceException $exception) {
            throw new OAuthServiceException($exception->getMessage());
        }
    }

    /**
     * @throws OAuthServiceException
     */
    private function generateRefreshToken(User $user, string $token, array $scopes): string
    {
        $payload = $this->jwtTokenService->generatePayload([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'token' => $token,
            ],
            'scopes' => $scopes,
            'refresh' => true,
        ], config('app.jwt_refresh_expiration'));

        try {
            return $this->jwtTokenService->encode($payload, storage_path('oauth-private.key'));
        } catch (JwtServiceException $exception) {
            throw new OAuthServiceException($exception->getMessage());
        }
    }
}
