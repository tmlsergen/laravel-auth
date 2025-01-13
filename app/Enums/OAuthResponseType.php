<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\BaseEnum;

enum OAuthResponseType: string
{
    use BaseEnum;

    case CODE = 'code';
}
