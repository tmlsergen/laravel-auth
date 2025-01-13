<?php

declare(strict_types=1);

namespace App\Enums;

enum RateLimit: int
{
    case MAX_ATTEMPTS = 3;
    case LOCKOUT_MINUTES = 5;
    case DECAY_MINUTES = 1;
    case WEB_MAX_ATTEMPTS = 150;
    case API_MAX_ATTEMPTS = 100;
}
