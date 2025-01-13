<?php

namespace App\Services\OAuth;

use App\Exceptions\JwtServiceException;
use App\Exceptions\OAuthServiceException;
use App\Exceptions\RepositoryException;
use App\Models\OAuthClient;
use App\Repositories\OAuthAccessTokenRepository;
use App\Services\JwtTokenService;
use Random\RandomException;

class ClientCredentialsGrantService implements GrantServiceInterface
{
    public function __construct(
        private OAuthAccessTokenRepository $oauthAccessTokenRepository,
        private JwtTokenService $jwtTokenService,
    ) {
    }

    /**
     * @throws OAuthServiceException
     */
    public function generateTokenResult(array $params): array
    {
        if (! isset($params['scope'])) {
            throw new OAuthServiceException('Scope is required');
        }

        try {
            $scopes = explode(' ', $params['scope']);
            if (count(array_diff($scopes, $params['client']->scopes)) > 0) {
                throw new OAuthServiceException('Invalid scope');
            }
            $expires = (int) config('app.jwt_expiration');

            $token = bin2hex(random_bytes(4).$params['client']->id);

            $tokenResult = $this->generateAccessToken(
                $params['client'],
                $scopes,
                $token
            );

            $this->oauthAccessTokenRepository->create([
                'access_token' => $token,
                'client_id' => $params['client']->client_id,
                'scope' => $params['scope'],
                'expires' => now()->addSeconds($expires),
            ]);

            return [
                'token_type' => 'Bearer',
                'expires_in' => now()->addSeconds($expires),
                'access_token' => $tokenResult,
            ];
        } catch (RandomException) {
            throw new OAuthServiceException('Internal server error');
        } catch (RepositoryException $e) {
            throw new OAuthServiceException($e->getMessage());
        }
    }

    /**
     * @throws OAuthServiceException
     */
    public function generateAccessToken(OAuthClient $client, array $scopes, string $token): string
    {
        $payload = $this->jwtTokenService->generatePayload([
            'user' => [
                'id' => $client->id,
                'client_id' => $client->client_id,
                'name' => $client->name,
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
}
