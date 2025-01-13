<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\BaseEnum;

enum OAuthScope: string
{
    use BaseEnum;
    case PROFILE = 'profile';
    case PROFILE_READONLY = 'profile.readonly';

    public static function permissions(string $scope): array
    {
        return match ($scope) {
            self::PROFILE->value => [
                'api.user.me',
                'api.user.me.update',
            ],
            self::PROFILE_READONLY->value => [
                'api.user.me',
            ],
            default => [],
        };
    }
}
