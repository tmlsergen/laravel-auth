<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Jwt as JwtEnum;
use App\Exceptions\JwtServiceException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use stdClass;

class JwtTokenService
{
    private string $key;

    public function __construct()
    {
        $this->key = config('app.jwt_secret');
    }

    /**
     * @return array{data: array, iss: mixed, iat: int, exp: mixed}
     */
    public function generatePayload(array $data, int $expiration = 0): array
    {
        $expire = time() + config('app.jwt_expiration');
        if ($expiration > 0) {
            $expire = time() + $expiration;
        }

        return [
            'data' => $data,
            'iss' => config('app.url'),
            'iat' => time(),
            'exp' => $expire,
        ];
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @throws JwtServiceException
     */
    public function encode(array $payload, ?string $filePath = null): string
    {
        if ($filePath) {
            $privateFile = file_get_contents($filePath) ?: throw new JwtServiceException('Private key not found');

            return JWT::encode($this->generatePayload($payload), $privateFile, JwtEnum::AUTH_ALGO->value);
        }

        return JWT::encode($this->generatePayload($payload), $this->key, JwtEnum::REST_PASSWORD_ALGO->value);
    }

    /**
     * @throws JwtServiceException
     */
    public function decode(string $token, ?string $filePath = null): stdClass
    {
        try {
            if ($filePath) {
                $publicFile = file_get_contents($filePath) ?: throw new JwtServiceException('Public key not found');

                return JWT::decode($token, new Key($publicFile, JwtEnum::AUTH_ALGO->value));
            }

            return JWT::decode($token, new Key($this->key, JwtEnum::REST_PASSWORD_ALGO->value));
        } catch (ExpiredException) {
            throw new JwtServiceException('Token expired');
        } catch (\Throwable $exception) {
            Log::error('JwtTokenService@decode', [
                'token' => $token,
                'error' => $exception->getMessage(),
            ]);

            throw new JwtServiceException('Invalid token');
        }
    }
}
