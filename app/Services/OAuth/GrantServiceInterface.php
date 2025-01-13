<?php

namespace App\Services\OAuth;

use App\Exceptions\OAuthServiceException;

interface GrantServiceInterface
{
    /**
     * @throws OAuthServiceException
     */
    public function generateTokenResult(array $params): array;
}
