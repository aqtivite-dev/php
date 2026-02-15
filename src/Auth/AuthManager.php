<?php

namespace Aqtivite\Php\Auth;

use Aqtivite\Php\Config;
use Aqtivite\Php\Contracts\HttpTransportInterface;
use Aqtivite\Php\Exceptions\AqtiviteException;
use Aqtivite\Php\Exceptions\AuthenticationException;

class AuthManager
{
    private ?Token $token = null;
    private ?CredentialInterface $credential = null;
    private ?\Closure $onTokenRefresh = null;

    public function __construct(
        private readonly Config $config,
        private HttpTransportInterface $transport,
    ) {}

    public function setTransport(HttpTransportInterface $transport): void
    {
        $this->transport = $transport;
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

    public function onTokenRefresh(\Closure $callback): void
    {
        $this->onTokenRefresh = $callback;
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

    private function updateToken(Token $token): Token
    {
        $this->token = $token;

        if ($this->onTokenRefresh) {
            ($this->onTokenRefresh)($token);
        }

        return $token;
    }

    private function checkToken(): bool
    {
        if (!$this->token) {
            return false;
        }

        try {
            $response = $this->transport->send('GET', $this->config->getBaseUrl() . '/auth', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->accessToken,
                ],
            ]);

            return ($response->body['status'] ?? false) === true;
        } catch (AqtiviteException) {
            return false;
        }
    }

    private function refreshToken(): Token
    {
        if (!$this->token?->refreshToken) {
            throw new AuthenticationException('No refresh token available.');
        }

        try {
            $response = $this->transport->send('POST', $this->config->getBaseUrl() . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->token->refreshToken,
                    'client_id' => $this->config->clientId,
                    'client_secret' => $this->config->clientSecret,
                    'scope' => '',
                ],
            ]);
        } catch (AqtiviteException $e) {
            throw new AuthenticationException('Token refresh failed: ' . $e->getMessage(), previous: $e);
        }

        $body = $response->body;

        if (isset($body['error'])) {
            throw new AuthenticationException($body['message'] ?? $body['error_description'] ?? 'Token refresh failed.');
        }

        return $this->updateToken(new Token(
            accessToken: $body['access_token'],
            refreshToken: $body['refresh_token'] ?? null,
            tokenType: $body['token_type'] ?? 'Bearer',
            expiresIn: $body['expires_in'] ?? null,
        ));
    }

    private function authenticate(): Token
    {
        if (!$this->credential) {
            throw new AuthenticationException('No credentials configured. Use setAccount() or setApiKey() first.');
        }

        $formData = $this->credential->toRequestBody(
            $this->config->clientId,
            $this->config->clientSecret,
        );

        try {
            $response = $this->transport->send('POST', $this->config->getBaseUrl() . '/oauth/token', [
                'form_params' => $formData,
            ]);
        } catch (AqtiviteException $e) {
            throw new AuthenticationException('Authentication failed: ' . $e->getMessage(), previous: $e);
        }

        $body = $response->body;

        if (isset($body['error'])) {
            throw new AuthenticationException($body['message'] ?? $body['error_description'] ?? 'Authentication failed.');
        }

        return $this->updateToken(new Token(
            accessToken: $body['access_token'],
            refreshToken: $body['refresh_token'] ?? null,
            tokenType: $body['token_type'] ?? 'Bearer',
            expiresIn: $body['expires_in'] ?? null,
        ));
    }
}
