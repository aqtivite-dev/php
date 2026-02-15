<?php

namespace Aqtivite\Php\Auth;

use Aqtivite\Php\Config;
use Aqtivite\Php\Exceptions\AuthenticationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AuthManager
{
    private Client $guzzle;
    private ?Token $token = null;
    private ?CredentialInterface $credential = null;

    public function __construct(
        private readonly Config $config,
    ) {
        $this->guzzle = new Client([
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function setCredential(CredentialInterface $credential): void
    {
        $this->credential = $credential;
    }

    public function setToken(string $accessToken, ?string $refreshToken = null): void
    {
        $this->token = new Token(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
        );
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function login(): Token
    {
        // 1. Token varsa geçerliliğini kontrol et
        if ($this->token) {
            if ($this->checkToken()) {
                return $this->token;
            }

            // 2. Token geçersizse refresh_token ile yenile
            if ($this->token->refreshToken) {
                try {
                    return $this->refreshToken();
                } catch (AuthenticationException) {
                    // Refresh başarısız, credential ile giriş yapılacak
                }
            }
        }

        // 3. Credential ile giriş yap
        return $this->authenticate();
    }

    private function checkToken(): bool
    {
        if (!$this->token) {
            return false;
        }

        try {
            $response = $this->guzzle->get(
                $this->config->getBaseUrl() . '/auth',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token->accessToken,
                    ],
                ],
            );

            $body = json_decode($response->getBody()->getContents(), true) ?? [];

            return ($body['status'] ?? false) === true;
        } catch (GuzzleException) {
            return false;
        }
    }

    private function refreshToken(): Token
    {
        if (!$this->token?->refreshToken) {
            throw new AuthenticationException('No refresh token available.');
        }

        try {
            $response = $this->guzzle->post(
                $this->config->getBaseUrl() . '/oauth/token',
                [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $this->token->refreshToken,
                        'client_id' => $this->config->clientId,
                        'client_secret' => $this->config->clientSecret,
                        'scope' => '',
                    ],
                ],
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Token refresh failed: ' . $e->getMessage(), previous: $e);
        }

        $body = json_decode($response->getBody()->getContents(), true) ?? [];

        if (isset($body['error'])) {
            throw new AuthenticationException($body['message'] ?? $body['error_description'] ?? 'Token refresh failed.');
        }

        $this->token = new Token(
            accessToken: $body['access_token'],
            refreshToken: $body['refresh_token'] ?? null,
            tokenType: $body['token_type'] ?? 'Bearer',
            expiresIn: $body['expires_in'] ?? null,
        );

        return $this->token;
    }

    private function authenticate(): Token
    {
        if (!$this->credential) {
            throw new AuthenticationException('No credentials configured. Use setAccount() or setApiKey() first.');
        }

        $body = $this->credential->toRequestBody(
            $this->config->clientId,
            $this->config->clientSecret,
        );

        try {
            $response = $this->guzzle->post(
                $this->config->getBaseUrl() . '/oauth/token',
                ['form_params' => $body],
            );
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Authentication failed: ' . $e->getMessage(), previous: $e);
        }

        $result = json_decode($response->getBody()->getContents(), true) ?? [];

        if (isset($result['error'])) {
            throw new AuthenticationException($result['message'] ?? $result['error_description'] ?? 'Authentication failed.');
        }

        $this->token = new Token(
            accessToken: $result['access_token'],
            refreshToken: $result['refresh_token'] ?? null,
            tokenType: $result['token_type'] ?? 'Bearer',
            expiresIn: $result['expires_in'] ?? null,
        );

        return $this->token;
    }
}
