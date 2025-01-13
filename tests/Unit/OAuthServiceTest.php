<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\OAuthGrantType;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServiceException;
use App\Models\OAuthAccessToken;
use App\Models\OAuthClient;
use App\Repositories\OAuthAccessTokenRepository;
use App\Repositories\OAuthClientRepository;
use App\Services\OAuth\AuthorizationCodeGrantService;
use App\Services\OAuth\GrantServiceFactory;
use App\Services\OAuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

final class OAuthServiceTest extends TestCase
{
    private OAuthService $oauthService;
    private OAuthClientRepository $clientRepository;
    private OAuthAccessTokenRepository $accessTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepository = Mockery::mock(OAuthClientRepository::class);
        $this->accessTokenRepository = Mockery::mock(OAuthAccessTokenRepository::class);

        $this->oauthService = new OAuthService(
            $this->clientRepository,
            $this->accessTokenRepository
        );
    }

    public function test_generate_credentials_successful(): void
    {
        // Arrange
        $clientId = 'test-client';
        $redirectUri = 'https://example.com/callback';
        $scope = 'read write';
        $state = 'random-state';

        $client = new OAuthClient();
        $client->forceFill([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scopes' => ['read', 'write'],
            'grant_types' => [OAuthGrantType::AUTHORIZATION_CODE->value],
        ]);

        $this->clientRepository
            ->shouldReceive('findByClientId')
            ->with($clientId)
            ->andReturn($client);

        // Act
        $result = $this->oauthService->generateCredentials([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $state,
        ]);

        // Assert
        $this->assertEquals([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scopes' => ['read', 'write'],
            'state' => $state,
        ], $result);
    }

    public function test_generate_code_successful(): void
    {
        // Arrange
        $clientId = 'test-client';
        $redirectUri = 'https://example.com/callback';
        $scopes = 'read write';
        $state = 'random-state';
        $userId = 1;

        Auth::shouldReceive('id')->andReturn($userId);

        Cache::shouldReceive('get')
            ->andReturn([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scopes' => ['read', 'write'],
                'state' => $state,
            ]);

        Cache::shouldReceive('put')->once();

        // Act
        $result = $this->oauthService->generateCode([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scopes' => $scopes,
            'state' => $state,
        ]);

        // Assert
        $this->assertStringContainsString($redirectUri, $result);
        $this->assertStringContainsString('code=', $result);
        $this->assertStringContainsString('state='.$state, $result);
    }

    public function test_check_token_successful(): void
    {
        // Arrange
        $token = 'valid-token';
        $accessToken = new OAuthAccessToken();

        $this->accessTokenRepository
            ->shouldReceive('findByToken')
            ->with($token)
            ->andReturn($accessToken);

        // Act
        $this->oauthService->checkToken($token);

        // Assert
        $this->assertTrue(true); // No exception thrown
    }

    public function test_check_token_invalid(): void
    {
        // Arrange
        $token = 'invalid-token';

        $this->accessTokenRepository
            ->shouldReceive('findByToken')
            ->with($token)
            ->andThrow(new NotFoundException('Token not found'));

        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Invalid token');

        // Act
        $this->oauthService->checkToken($token);
    }

    public function test_revoke_token_successful(): void
    {
        // Arrange
        $token = 'valid-token';
        $accessToken = new OAuthAccessToken();
        $accessToken->forceFill(['id' => 1]);

        $claim = (object) [
            'data' => (object) [
                'data' => (object) [
                    'user' => (object) [
                        'token' => $token
                    ]
                ]
            ]
        ];

        $this->accessTokenRepository
            ->shouldReceive('findByToken')
            ->with($token)
            ->andReturn($accessToken);

        $this->accessTokenRepository
            ->shouldReceive('deleteById')
            ->with($accessToken->id)
            ->once();

        // Act
        $this->oauthService->revokeToken($claim);

        // Assert
        $this->assertTrue(true); // No exception thrown
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
