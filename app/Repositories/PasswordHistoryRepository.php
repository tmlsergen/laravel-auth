<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\PasswordHistory;

class PasswordHistoryRepository extends AbstractRepository
{
    public function __construct(PasswordHistory $model)
    {
        parent::__construct($model);
    }
}
