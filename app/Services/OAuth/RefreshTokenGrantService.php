<?php

namespace App\Services\OAuth;

use App\Exceptions\JwtServiceException;
use App\Exceptions\NotFoundException;
use App\Exceptions\OAuthServiceException;
use App\Models\User;
use App\Repositories\OAuthAccessTokenRepository;
use App\Repositories\UserRepository;
use App\Services\JwtTokenService;

class RefreshTokenGrantService implements GrantServiceInterface
{
    public function __construct(
        private OAuthAccessTokenRepository $oauthAccessTokenRepository,
        private JwtTokenService $jwtTokenService,
        private UserRepository $userRepository
    ) {
    }

    public function generateTokenResult(array $params): array
    {
        try {
            $claim = $this->jwtTokenService->decode($params['refresh_token'], storage_path('oauth-public.key'));
            $token = $claim->data->data->user->token;
            $userId = $claim->data->data->user->id;
            $scopes = $claim->data->data->scopes;

            if ($claim->data->data?->refresh !== true) {
                throw new OAuthServiceException('Invalid refresh token');
            }

            if (isset($params['scope'])) {
                $scopes = explode(' ', $params['scope']);
                if (count(array_diff($scopes, $params['client']->scopes)) > 0) {
                    throw new OAuthServiceException('Invalid scope');
                }
            }

            /** @var User $user */
            $user = $this->userRepository->findById($userId);

            $newToken = bin2hex(random_bytes(4).$user->id);

            $this->oauthAccessTokenRepository->findByToken($token);

            $accessToken = $this->generateAccessToken($user, $newToken, $scopes);
            $refreshToken = $this->generateRefreshToken($user, $newToken, $scopes);
            $expire = (int) config('app.jwt_expiration');

            $this->oauthAccessTokenRepository->updateByToken([
                'access_token' => $newToken,
                'scope' => implode(' ', $scopes),
                'expires' => now()->addSeconds($expire),
            ], $token);

            return [
                'token_type' => 'Bearer',
                'expires_in' => now()->addSeconds($expire),
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ];
        } catch (NotFoundException|JwtServiceException) {
            throw new OAuthServiceException('Invalid refresh token');
        } catch (\Exception $exception) {
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
