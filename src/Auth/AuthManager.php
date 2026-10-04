<?php

namespace Aqtivite\Php\Auth;

use Aqtivite\Php\Config;
use Aqtivite\Php\Contracts\HttpTransportInterface;
use Aqtivite\Php\Exceptions\AqtiviteException;
use Aqtivite\Php\Exceptions\AuthenticationException;
use DateTimeImmutable;

class AuthManager
{
    private ?Token $token = null;
    private ?\Closure $onTokenIssued = null;

    public function __construct(
        private readonly Config $config,
        private HttpTransportInterface $transport,
    ) {}

    public function setTransport(HttpTransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function setToken(string $accessToken, ?DateTimeImmutable $expiresAt = null): void
    {
        $this->token = new Token(
            accessToken: $accessToken,
            expiresAt: $expiresAt,
        );
    }

    public function onTokenIssued(\Closure $callback): void
    {
        $this->onTokenIssued = $callback;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function token(): Token
    {
        if ($this->token === null || $this->token->isExpired()) {
            return $this->issueToken();
        }

        return $this->token;
    }

    public function issueToken(): Token
    {
        try {
            $response = $this->transport->send('POST', $this->config->getBaseUrl() . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->config->clientId,
                    'client_secret' => $this->config->clientSecret,
                    'scope' => implode(' ', $this->config->getScopes()),
                ],
            ]);
        } catch (AqtiviteException $e) {
            throw new AuthenticationException('Authentication failed: ' . $e->getMessage(), previous: $e);
        }

        $body = $response->body;

        if (empty($body['access_token'])) {
            throw new AuthenticationException($body['message'] ?? $body['error_description'] ?? 'Authentication failed.');
        }

        $this->token = Token::fromResponse($body);

        if ($this->onTokenIssued) {
            ($this->onTokenIssued)($this->token);
        }

        return $this->token;
    }
}
