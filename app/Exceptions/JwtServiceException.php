<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class JwtServiceException extends Exception
{
    public function __construct($message = 'Jwt Service Exception', $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
