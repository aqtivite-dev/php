<?php

namespace Aqtivite\Php\Exceptions;

class ApiException extends AqtiviteException
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?string $errorType = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
