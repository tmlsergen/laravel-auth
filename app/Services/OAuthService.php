<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OAuthGrantType;
use App\Exceptions\NotFoundException;
use App\Exceptions\OAuthServiceException;
use App\Exceptions\RepositoryException;
use App\Exceptions\ServiceException;
use App\Repositories\OAuthAccessTokenRepository;
use App\Repositories\OAuthClientRepository;
use App\Services\OAuth\GrantServiceFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Random\RandomException;

readonly class OAuthService
{
    public function __construct(
        private OAuthClientRepository $oauthClientRepository,
        private OAuthAccessTokenRepository $oauthAccessTokenRepository
    ) {
    }

    /**
     * @throws ServiceException
     */
    public function generateCredentials(array $reqParams): array
    {
        $state = '';
        if (isset($reqParams['state'])) {
            $state = $reqParams['state'];
        }

        try {
            $client = $this->oauthClientRepository->findByClientId($reqParams['client_id']);

            $redirectUri = $client->redirect_uri;
            if (isset($reqParams['redirect_uri'])) {
                $redirectUri = $reqParams['redirect_uri'];
            }

            $requestScopes = explode(' ', $reqParams['scope']);

            $scopes = [];
            foreach ($client->scopes as $scope) {
                if (! in_array($scope, $requestScopes)) {
                    continue;
                }

                $scopes[] = $scope;
            }

            $grantTypes = $client->grant_types;
            if (! in_array(OAuthGrantType::AUTHORIZATION_CODE->value, $grantTypes)) {
                throw new ServiceException('Invalid grant type');
            }

            $parameters = [
                'client_id' => $client->client_id,
                'redirect_uri' => $redirectUri,
                'scopes' => $scopes,
                'state' => $state,
            ];
            Cache::put($this->getCacheKey(), $parameters);

            return $parameters;
        } catch (NotFoundException) {
            throw new ServiceException('Client not found');
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws RandomException
     * @throws ServiceException
     */
    public function generateCode(array $params): string
    {
        $session = Cache::get($this->getCacheKey(), [
            'client_id' => '',
            'redirect_uri' => '',
            'scopes' => [],
            'state' => '',
        ]);

        if ($session['client_id'] !== $params['client_id']) {
            throw new ServiceException('Invalid client id');
        }

        $reqScopes = explode(' ', $params['scopes']);
        $sessionScopes = $session['scopes'];
        if (count(array_diff($reqScopes, $sessionScopes)) > 0) {
            throw new ServiceException('Invalid scopes');
        }

        if (isset($params['state']) && $session['state'] !== $params['state']) {
            throw new ServiceException('Invalid state');
        }

        if ($params['redirect_uri'] !== $session['redirect_uri']) {
            throw new ServiceException('Invalid redirect uri');
        }

        $code = bin2hex(random_bytes(8).Auth::id());

        $redirect = $session['redirect_uri'].'?code='.$code;
        if ($session['state'] != '') {
            $redirect .= '&state='.$session['state'];
        }

        $session['code'] = $code;
        $session['user_id'] = Auth::id();
        Cache::put($code, $session);

        return $redirect;
    }

    /**
     * @throws ServiceException
     */
    public function generateToken(array $params): array
    {
        try {
            $grantService = GrantServiceFactory::create($params['grant_type']);

            $client = $this->oauthClientRepository->findByClientId($params['client_id']);
            if ($client->client_secret != $params['client_secret']) {
                throw new ServiceException('Invalid client secret');
            }

            $params['client'] = $client;

            return $grantService->generateTokenResult($params);
        } catch (NotFoundException) {
            throw new ServiceException('Client not found');
        } catch (RepositoryException|OAuthServiceException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    private function getCacheKey(): string
    {
        return 'oauth_authorize_'.Auth::id();
    }

    public function removeExpiredTokens(): void
    {
        try {
            $this->oauthAccessTokenRepository->removeExpiredTokens();
        } catch (RepositoryException) {
            return;
        }

    }

    /**
     * @throws ServiceException
     */
    public function checkToken(string $token)
    {
        try {
            $this->oauthAccessTokenRepository->findByToken($token);
        } catch (NotFoundException) {
            throw new ServiceException('Invalid token');
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @throws ServiceException
     */
    public function revokeToken(\stdClass $claim): void
    {
        $token = $claim->data->data->user->token;

        try {
            $accessToken = $this->oauthAccessTokenRepository->findByToken($token);
            $this->oauthAccessTokenRepository->deleteById($accessToken->id);
        } catch (NotFoundException) {
            throw new ServiceException('Unauthorized');
        } catch (RepositoryException $e) {
            throw new ServiceException($e->getMessage());
        }
    }
}
