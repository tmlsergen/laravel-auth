<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ServiceException extends Exception
{
    public function __construct($message = 'Service error', $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
