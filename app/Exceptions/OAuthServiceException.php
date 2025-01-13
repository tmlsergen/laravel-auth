<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class OAuthServiceException extends Exception
{
    public function __construct($message = 'OAuth Error', $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
