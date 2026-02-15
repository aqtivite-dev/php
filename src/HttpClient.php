<?php

namespace Aqtivite\Php;

use Aqtivite\Php\Auth\AuthManager;
use Aqtivite\Php\Exceptions\ApiException;
use Aqtivite\Php\Response\ApiResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpClient
{
    private Client $guzzle;

    public function __construct(
        private readonly Config $config,
        private readonly AuthManager $auth,
    ) {
        $this->guzzle = new Client([
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function get(string $path, array $query = []): ApiResponse
    {
        return $this->request('GET', $path, ['query' => $query]);
    }

    public function post(string $path, array $data = [], string $contentType = 'json'): ApiResponse
    {
        $options = match ($contentType) {
            'json' => ['json' => $data],
            'form' => ['form_params' => $data],
            'multipart' => ['multipart' => $data],
            default => ['json' => $data],
        };

        return $this->request('POST', $path, $options);
    }

    public function put(string $path, array $data = []): ApiResponse
    {
        return $this->request('PUT', $path, ['json' => $data]);
    }

    public function patch(string $path, array $data = []): ApiResponse
    {
        return $this->request('PATCH', $path, ['json' => $data]);
    }

    public function delete(string $path): ApiResponse
    {
        return $this->request('DELETE', $path);
    }

    private function request(string $method, string $path, array $options = [], bool $retried = false): ApiResponse
    {
        $url = $this->config->getBaseUrl() . $path;

        $token = $this->auth->getToken();
        if ($token) {
            $options['headers']['Authorization'] = 'Bearer ' . $token->accessToken;
        }

        try {
            $response = $this->guzzle->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new ApiException(
                message: 'HTTP request failed: ' . $e->getMessage(),
                code: $e->getCode(),
                previous: $e,
            );
        }

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true) ?? [];

        if ($statusCode === 401 && !$retried) {
            $this->auth->login();

            return $this->request($method, $path, $options, retried: true);
        }

        if (isset($body['status']) && $body['status'] === false) {
            $error = $body['error'] ?? [];

            throw new ApiException(
                message: $error['description'] ?? 'API request failed',
                code: $error['code'] ?? $statusCode,
                errorType: $error['type'] ?? null,
            );
        }

        return ApiResponse::fromArray($body);
    }
}
