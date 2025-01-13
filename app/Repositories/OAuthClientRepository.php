<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Exceptions\RepositoryException;
use App\Models\OAuthClient;
use Illuminate\Support\Facades\Log;

class OAuthClientRepository extends AbstractRepository
{
    public function __construct(OAuthClient $model)
    {
        parent::__construct($model);
    }

    /**
     * @throws NotFoundException
     * @throws RepositoryException
     */
    public function findByClientId(string $clientId): OAuthClient
    {
        try {
            $model = $this->model->newQuery()->where('client_id', $clientId)->first();
            if (! $model) {
                throw new NotFoundException(class_basename(OAuthClient::class).' not found');
            }

            return $model;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::channel('repository')->error('AbstractRepository@findById', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }
    }
}
