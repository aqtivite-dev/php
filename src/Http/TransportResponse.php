<?php

namespace Aqtivite\Php\Http;

class TransportResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly array $body,
    ) {}
}
