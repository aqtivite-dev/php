<?php

namespace Aqtivite\Php;

use Aqtivite\Php\Auth\AuthManager;
use Aqtivite\Php\Contracts\HttpTransportInterface;
use Aqtivite\Php\Exceptions\ApiException;
use Aqtivite\Php\Response\ApiResponse;

class HttpClient
{
    public function __construct(
        private readonly Config $config,
        private readonly AuthManager $auth,
        private HttpTransportInterface $transport,
    ) {}

    public function setTransport(HttpTransportInterface $transport): void
    {
        $this->transport = $transport;
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

        $response = $this->transport->send($method, $url, $options);

        if ($response->statusCode === 401 && !$retried) {
            $this->auth->login();

            return $this->request($method, $path, $options, retried: true);
        }

        $body = $response->body;

        if (isset($body['status']) && $body['status'] === false) {
            $error = $body['error'] ?? [];

            throw new ApiException(
                message: $error['description'] ?? 'API request failed',
                code: $error['code'] ?? $response->statusCode,
                errorType: $error['type'] ?? null,
            );
        }

        return ApiResponse::fromArray($body);
    }
}
