<?php

namespace Aqtivite\Php\Http;

use Aqtivite\Php\Contracts\HttpTransportInterface;
use Aqtivite\Php\Exceptions\AqtiviteException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleTransport implements HttpTransportInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function send(string $method, string $url, array $options = []): TransportResponse
    {
        try {
            $response = $this->client->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new AqtiviteException(
                message: 'HTTP request failed: ' . $e->getMessage(),
                code: $e->getCode(),
                previous: $e,
            );
        }

        $body = json_decode($response->getBody()->getContents(), true) ?? [];

        return new TransportResponse(
            statusCode: $response->getStatusCode(),
            body: $body,
        );
    }
}
