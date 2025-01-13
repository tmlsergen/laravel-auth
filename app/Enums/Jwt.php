<?php

declare(strict_types=1);

namespace App\Enums;

enum Jwt: string
{
    case REST_PASSWORD_ALGO = 'HS256';
    case AUTH_ALGO = 'RS256';
}
