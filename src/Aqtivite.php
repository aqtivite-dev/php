<?php

namespace Aqtivite\Php;

use Aqtivite\Php\Auth\AuthManager;
use Aqtivite\Php\Auth\Token;
use Aqtivite\Php\Concerns\Macroable;
use Aqtivite\Php\Contracts\HttpTransportInterface;
use Aqtivite\Php\Http\GuzzleTransport;
use Aqtivite\Php\Modules\CommonModule;
use Aqtivite\Php\Modules\UserModule;
use DateTimeImmutable;

class Aqtivite
{
    use Macroable;

    private Config $config;
    private AuthManager $auth;
    private HttpClient $http;

    /**
     * @param  string[]  $scopes
     */
    public function __construct(string $clientId, string $clientSecret, array $scopes = [])
    {
        $this->config = new Config($clientId, $clientSecret, $scopes);

        $transport = new GuzzleTransport();
        $this->auth = new AuthManager($this->config, $transport);
        $this->http = new HttpClient($this->config, $this->auth, $transport);
    }

    public function setTransport(HttpTransportInterface $transport): static
    {
        $this->auth->setTransport($transport);
        $this->http->setTransport($transport);

        return $this;
    }

    /**
     * @param  string[]  $scopes
     */
    public function setScopes(array $scopes): static
    {
        $this->config->setScopes($scopes);

        return $this;
    }

    public function setToken(string $accessToken, ?DateTimeImmutable $expiresAt = null): static
    {
        $this->auth->setToken($accessToken, $expiresAt);

        return $this;
    }

    public function onTokenIssued(\Closure $callback): static
    {
        $this->auth->onTokenIssued($callback);

        return $this;
    }

    public function issueToken(): Token
    {
        return $this->auth->issueToken();
    }

    public function testMode(bool $enabled = true): static
    {
        $this->config->setTestMode($enabled);

        return $this;
    }

    public function setBaseUrl(string $url): static
    {
        $this->config->setBaseUrl($url);

        return $this;
    }

    public function user(): UserModule
    {
        return new UserModule($this->http);
    }

    public function common(): CommonModule
    {
        return new CommonModule($this->http);
    }

    public function getToken(): ?Token
    {
        return $this->auth->getToken();
    }

    public function getHttpClient(): HttpClient
    {
        return $this->http;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
