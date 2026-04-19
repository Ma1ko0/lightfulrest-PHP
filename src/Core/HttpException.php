<?php

declare(strict_types=1);

namespace App;

use Exception;

class HttpException extends Exception
{
    private int $statusCode;

    public function __construct(string $message, int $statusCode = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
