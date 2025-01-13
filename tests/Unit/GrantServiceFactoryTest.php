<?php

namespace Tests\Unit;

use App\Services\OAuth\AuthorizationCodeGrantService;
use App\Services\OAuth\ClientCredentialsGrantService;
use App\Services\OAuth\GrantServiceFactory;
use App\Services\OAuth\RefreshTokenGrantService;
use Tests\TestCase;

class GrantServiceFactoryTest extends TestCase
{
    public function testCreateClientCredentialsGrantService(): void
    {
        $grantType = 'client_credentials';
        $grantService = GrantServiceFactory::create($grantType);

        $this->assertInstanceOf(ClientCredentialsGrantService::class, $grantService);
    }

    public function testCreateAuthorizationCodeGrantService(): void
    {
        $grantType = 'authorization_code';
        $grantService = GrantServiceFactory::create($grantType);

        $this->assertInstanceOf(AuthorizationCodeGrantService::class, $grantService);
    }

    public function testCreateRefreshTokenGrantService(): void
    {
        $grantType = 'refresh_token';
        $grantService = GrantServiceFactory::create($grantType);

        $this->assertInstanceOf(RefreshTokenGrantService::class, $grantService);
    }

    public function testCreateUnsupportedGrantType(): void
    {
        $grantType = 'unsupported_grant_type';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported grant type');

        GrantServiceFactory::create($grantType);
    }
}
