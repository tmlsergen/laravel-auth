<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Exceptions\RepositoryException;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserRepository extends AbstractRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @throws RepositoryException
     * @throws NotFoundException
     */
    public function findByEmail(string $email): User
    {
        try {
            $model = $this->model->newQuery()->where('email', $email)->first();
            if (! $model) {
                throw new NotFoundException(class_basename($this->model).' not found');
            }

            return $model;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::channel('repository')->error('UserRepository@findByEmail', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }
    }
}
