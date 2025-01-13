<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\JwtServiceException;
use App\Exceptions\OAuthServiceException;
use App\Exceptions\RepositoryException;
use App\Models\OAuthClient;
use App\Repositories\OAuthAccessTokenRepository;
use App\Services\JwtTokenService;
use App\Services\OAuth\ClientCredentialsGrantService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class ClientCredentialsGrantServiceTest extends TestCase
{
    use WithFaker;

    private ClientCredentialsGrantService $service;
    private OAuthAccessTokenRepository $tokenRepository;
    private JwtTokenService $jwtService;
    private OAuthClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenRepository = Mockery::mock(OAuthAccessTokenRepository::class);
        $this->jwtService = Mockery::mock(JwtTokenService::class);

        $this->service = new ClientCredentialsGrantService(
            $this->tokenRepository,
            $this->jwtService
        );

        // Test için örnek client oluşturma
        $this->client = new OAuthClient();
        $this->client->forceFill([
            'id' => 1,
            'client_id' => 'test_client_id',
            'name' => 'Test Client',
            'scopes' => ['read', 'write']
        ]);

        // Config mock
        config(['app.jwt_expiration' => 3600]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGenerateTokenResultSuccess(): void
    {
        $params = [
            'client' => $this->client,
            'scope' => 'read write'
        ];

        $expectedToken = 'generated_jwt_token';
        $expectedAccessToken = Mockery::type('string');

        $this->jwtService->shouldReceive('generatePayload')
            ->once()
            ->andReturn(['test' => 'payload']);

        $this->jwtService->shouldReceive('encode')
            ->once()
            ->andReturn($expectedToken);

        $this->tokenRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return isset($arg['access_token']) &&
                    $arg['client_id'] === 'test_client_id' &&
                    $arg['scope'] === 'read write' &&
                    $arg['expires'] instanceof Carbon;
            }))
            ->andReturn(true);

        $result = $this->service->generateTokenResult($params);

        $this->assertIsArray($result);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertEquals($expectedToken, $result['access_token']);
        $this->assertInstanceOf(Carbon::class, $result['expires_in']);
    }

    public function testGenerateTokenResultWithMissingScope(): void
    {
        $this->expectException(OAuthServiceException::class);
        $this->expectExceptionMessage('Scope is required');

        $this->service->generateTokenResult(['client' => $this->client]);
    }

    public function testGenerateTokenResultWithInvalidScope(): void
    {
        $this->expectException(OAuthServiceException::class);
        $this->expectExceptionMessage('Invalid scope');

        $params = [
            'client' => $this->client,
            'scope' => 'read write delete' // 'delete' is not in allowed scopes
        ];

        $this->service->generateTokenResult($params);
    }

    public function testGenerateTokenResultWithRepositoryError(): void
    {
        $params = [
            'client' => $this->client,
            'scope' => 'read write'
        ];

        $this->jwtService->shouldReceive('generatePayload')->once()->andReturn(['test' => 'payload']);
        $this->jwtService->shouldReceive('encode')->once()->andReturn('token');

        $this->tokenRepository->shouldReceive('create')
            ->once()
            ->andThrow(new RepositoryException('Database error'));

        $this->expectException(OAuthServiceException::class);
        $this->expectExceptionMessage('Database error');

        $this->service->generateTokenResult($params);
    }

    public function testGenerateAccessTokenSuccess(): void
    {
        $scopes = ['read', 'write'];
        $token = 'test_token';
        $expectedToken = 'encoded_jwt_token';

        $this->jwtService->shouldReceive('generatePayload')
            ->once()
            ->with(Mockery::on(function ($arg) use ($token) {
                return $arg['user']['client_id'] === 'test_client_id' &&
                    $arg['user']['token'] === $token;
            }), 3600)
            ->andReturn(['test' => 'payload']);

        $this->jwtService->shouldReceive('encode')
            ->once()
            ->with(['test' => 'payload'], storage_path('oauth-private.key'))
            ->andReturn($expectedToken);

        $result = $this->service->generateAccessToken($this->client, $scopes, $token);

        $this->assertEquals($expectedToken, $result);
    }

    public function testGenerateAccessTokenWithJwtError(): void
    {
        $scopes = ['read', 'write'];
        $token = 'test_token';

        $this->jwtService->shouldReceive('generatePayload')
            ->once()
            ->andReturn(['test' => 'payload']);

        $this->jwtService->shouldReceive('encode')
            ->once()
            ->andThrow(new JwtServiceException('JWT encoding failed'));

        $this->expectException(OAuthServiceException::class);
        $this->expectExceptionMessage('JWT encoding failed');

        $this->service->generateAccessToken($this->client, $scopes, $token);
    }
}
