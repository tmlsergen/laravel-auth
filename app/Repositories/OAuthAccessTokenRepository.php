<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Exceptions\RepositoryException;
use App\Models\OAuthAccessToken;
use Illuminate\Support\Facades\Log;

class OAuthAccessTokenRepository extends AbstractRepository
{
    public function __construct(OAuthAccessToken $model)
    {
        parent::__construct($model);
    }

    public function findByToken(string $token): OAuthAccessToken
    {
        try {
            $accessToken = $this->model->newQuery()->where('access_token', $token)->first();
            if (! $accessToken) {
                throw new NotFoundException(class_basename($this->model).' not found');
            }

            return $accessToken;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::channel('repository')->error('OAuthTokenRepository@findByToken', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }
    }

    /**
     * @throws RepositoryException
     */
    public function updateByToken(array $data, string $token): int
    {
        try {
            return $this->model->newQuery()
                ->where('access_token', $token)
                ->update($data);
        } catch (\Exception $e) {
            Log::channel('repository')->error('OAuthTokenRepository@updateByToken', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }
    }

    /**
     * @throws RepositoryException
     */
    public function removeExpiredTokens(): void
    {
        try {
            $this->model->newQuery()
                ->where('expires_at', '<', now())
                ->delete();
        } catch (\Exception $e) {
            Log::channel('repository')->error('OAuthTokenRepository@removeExpiredTokens', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }

    }
}
