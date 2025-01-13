<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\BaseEnum;

enum Role: string
{
    use BaseEnum;
    case ADMIN = 'admin';
    case USER = 'user';
}
