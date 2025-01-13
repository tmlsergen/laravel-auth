<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\JwtServiceException;
use App\Services\JwtTokenService;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use App\Enums\Jwt as JWTEnum;
use Tests\TestCase;

final class JwtTokenServiceTest extends TestCase
{
    private JwtTokenService $jwtTokenService;
    private string $testSecret = 'test-secret-key';
    private string $testUrl = 'http://localhost';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.jwt_secret', $this->testSecret);
        Config::set('app.url', $this->testUrl);
        Config::set('app.jwt_expiration', 3600);

        $this->jwtTokenService = new JwtTokenService();
    }

    public function test_generate_payload_with_default_expiration(): void
    {
        // Arrange
        $data = ['user_id' => 1, 'email' => 'test@example.com'];
        $currentTime = time();

        // Act
        $payload = $this->jwtTokenService->generatePayload($data);

        // Assert
        $this->assertEquals($data, $payload['data']);
        $this->assertEquals($this->testUrl, $payload['iss']);
        $this->assertGreaterThanOrEqual($currentTime, $payload['iat']);
        $this->assertEquals($currentTime + 3600, $payload['exp']);
    }

    public function test_generate_payload_with_custom_expiration(): void
    {
        // Arrange
        $data = ['user_id' => 1];
        $customExpiration = 7200;
        $currentTime = time();

        // Act
        $payload = $this->jwtTokenService->generatePayload($data, $customExpiration);

        // Assert
        $this->assertEquals($data, $payload['data']);
        $this->assertEquals($currentTime + $customExpiration, $payload['exp']);
    }

    public function test_encode_token_successful(): void
    {
        // Arrange
        $data = ['user_id' => 1, 'email' => 'test@example.com'];

        // Act
        $token = $this->jwtTokenService->encode($data);

        // Assert
        $this->assertIsString($token);

        // Verify token can be decoded
        $decoded = JWT::decode(
            $token,
            new \Firebase\JWT\Key($this->testSecret, JWTEnum::REST_PASSWORD_ALGO->value)
        );
        $this->assertEquals($data, (array) $decoded->data);
    }

    public function test_decode_token_successful(): void
    {
        // Arrange
        $data = ['user_id' => 1, 'email' => 'test@example.com'];
        $token = $this->jwtTokenService->encode($data);

        // Act
        $decoded = $this->jwtTokenService->decode($token);

        // Assert
        $this->assertEquals($data, (array) $decoded->data);
        $this->assertEquals($this->testUrl, $decoded->iss);
    }

    public function test_decode_token_with_public_key(): void
    {
        // Arrange
        $data = ['user_id' => 1];
        $publicKeyPath = __DIR__.'/../../Fixtures/public.key';
        $publicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvUBYuiIARI4MJg==
-----END PUBLIC KEY-----';

        if (!file_exists(dirname($publicKeyPath))) {
            mkdir(dirname($publicKeyPath), 0777, true);
        }
        file_put_contents($publicKeyPath, $publicKey);

        $this->jwtTokenService->setKey($publicKey);

        // Act & Assert
        $this->expectException(JwtServiceException::class);
        $this->expectExceptionMessage('Invalid token');

        $this->jwtTokenService->decode('invalid-token', $publicKeyPath);

        // Cleanup
        unlink($publicKeyPath);
    }

    public function test_decode_expired_token(): void
    {
        // Arrange
        $data = ['user_id' => 1];
        Config::set('app.jwt_expiration', -1); // Set expiration to past
        $token = $this->jwtTokenService->encode($data);

        // Assert
        $this->expectException(JwtServiceException::class);
        $this->expectExceptionMessage('Token expired');

        // Act
        $this->jwtTokenService->decode($token);
    }

    public function test_decode_invalid_token(): void
    {
        // Assert
        $this->expectException(JwtServiceException::class);
        $this->expectExceptionMessage('Invalid token');

        // Act
        $this->jwtTokenService->decode('invalid-token-string');
    }

    public function test_set_key(): void
    {
        // Arrange
        $newKey = 'new-secret-key';
        $data = ['user_id' => 1];

        // Act
        $this->jwtTokenService->setKey($newKey);
        $token = $this->jwtTokenService->encode($data);
        $decoded = $this->jwtTokenService->decode($token);

        // Assert
        $this->assertEquals($data, (array) $decoded->data);
    }
}
