<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthGuard: string
{
    case WEB = 'web';
    case API = 'api';
    case OAuth = 'oauth';
}
