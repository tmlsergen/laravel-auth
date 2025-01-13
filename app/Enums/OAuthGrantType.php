<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\BaseEnum;

enum OAuthGrantType: string
{
    use BaseEnum;
    case AUTHORIZATION_CODE = 'authorization_code';
    case CLIENT_CREDENTIALS = 'client_credentials';
    case REFRESH_TOKEN = 'refresh_token';
}
