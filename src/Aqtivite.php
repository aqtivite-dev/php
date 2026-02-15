<?php

namespace Aqtivite\Php;

use Aqtivite\Php\Auth\ApiCredential;
use Aqtivite\Php\Auth\AuthManager;
use Aqtivite\Php\Auth\PasswordCredential;
use Aqtivite\Php\Auth\Token;
use Aqtivite\Php\Contracts\HttpTransportInterface;
use Aqtivite\Php\Http\GuzzleTransport;
use Aqtivite\Php\Modules\CommonModule;
use Aqtivite\Php\Modules\UserModule;
use Aqtivite\Php\Response\ApiResponse;

class Aqtivite
{
    private Config $config;
    private AuthManager $auth;
    private HttpClient $http;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->config = new Config($clientId, $clientSecret);

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

    public function setAccount(string $username, string $password): static
    {
        $this->auth->setCredential(new PasswordCredential($username, $password));

        return $this;
    }

    public function setApiKey(string $apiKey, string $apiSecret): static
    {
        $this->auth->setCredential(new ApiCredential($apiKey, $apiSecret));

        return $this;
    }

    public function setToken(string $accessToken, ?string $refreshToken = null): static
    {
        $this->auth->setToken($accessToken, $refreshToken);

        return $this;
    }

    public function login(): Token
    {
        return $this->auth->login();
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

    public function me(): ApiResponse
    {
        return $this->http->get('/auth');
    }

    public function logout(): ApiResponse
    {
        return $this->http->delete('/auth');
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

    public function getConfig(): Config
    {
        return $this->config;
    }
}
