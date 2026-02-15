<?php

namespace Aqtivite\Php\Contracts;

use Aqtivite\Php\Http\TransportResponse;

interface HttpTransportInterface
{
    public function send(string $method, string $url, array $options = []): TransportResponse;
}
