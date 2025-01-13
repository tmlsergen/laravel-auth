<?php

namespace App\Services\OAuth;

use App\Enums\OAuthGrantType;

class GrantServiceFactory
{
    public static function create(string $grantType): GrantServiceInterface
    {
        return match ($grantType) {
            OAuthGrantType::CLIENT_CREDENTIALS->value => app(ClientCredentialsGrantService::class),
            OAuthGrantType::AUTHORIZATION_CODE->value => app(AuthorizationCodeGrantService::class),
            OAuthGrantType::REFRESH_TOKEN->value => app(RefreshTokenGrantService::class),
            default => throw new \InvalidArgumentException('Unsupported grant type'),
        };
    }
}
