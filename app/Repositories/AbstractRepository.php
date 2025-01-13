<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class AbstractRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @throws RepositoryException
     * @throws NotFoundException
     */
    public function findById(int $id): Model
    {
        try {
            $model = $this->model->newQuery()->find($id);
            if (! $model) {
                throw new NotFoundException(class_basename($this->model).' not found');
            }

            return $model;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::channel('repository')->error('AbstractRepository@findById', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }
    }

    /**
     * @throws RepositoryException
     */
    public function get(array $where = [], array $relations = [], array $columns = ['*'], array $orderBy = [], array $pagination = []): \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        try {
            $query = $this->model->newQuery();

            if (! empty($query)) {
                foreach ($where as $value) {
                    $query = $query->where($value['field'], $value['operator'] ?? '=', $value['value']);
                }
            }

            if (! empty($relations)) {
                $query = $query->with($relations);
            }

            if (! empty($orderBy)) {
                $query = $query->orderBy($orderBy);
            }

            if (isset($pagination['page']) && isset($pagination['limit'])) {
                return $query->paginate(
                    perPage: $pagination['limit'],
                    columns: $columns,
                    page: $pagination['page']
                );
            }

            return $query->get($columns);
        } catch (\Exception $e) {
            Log::channel('repository')->error('AbstractRepository@get', $this->getErrorContext($e));
            throw new RepositoryException('Internal server error');
        }
    }

    /**
     * @throws RepositoryException
     */
    public function create(array $data): Model|bool
    {
        try {
            return $this->model
                ->newQuery()
                ->create($data);
        } catch (\Exception $e) {
            Log::channel('repository')->error('AbstractRepository@create', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }
    }

    /**
     * @throws RepositoryException
     */
    public function updateById(array $data, int $id): int
    {
        try {
            return $this->model->newQuery()
                ->where('id', $id)
                ->update($data);
        } catch (\Exception $e) {
            Log::channel('repository')->error('AbstractRepository@updateById', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }
    }

    /**
     * @throws RepositoryException
     */
    public function deleteById(int $id): mixed
    {
        try {
            return $this->model->newQuery()->where('id', $id)->delete();
        } catch (\Exception $e) {
            Log::channel('repository')->error('AbstractRepository@deleteById', $this->getErrorContext($e));

            throw new RepositoryException('Internal server error');
        }

    }

    protected function getErrorContext(\Exception $e): array
    {
        return [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
        ];
    }
}
