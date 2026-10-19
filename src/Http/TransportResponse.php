<?php

namespace Aqtivite\Php\Http;

class TransportResponse
{
    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $body,
        public readonly array $headers = [],
    ) {}

    public function header(string $name): ?string
    {
        return array_change_key_case($this->headers)[strtolower($name)] ?? null;
    }
}
