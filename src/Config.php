<?php

namespace Aqtivite\Php;

class Config
{
    private const BASE_URL = 'https://api.aqtivite.com.tr';
    private const TEST_URL = 'https://api.test.aqtivite.com.tr';

    private bool $testMode = false;
    private ?string $baseUrlOverride = null;

    /**
     * @param  string[]  $scopes
     */
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        private array $scopes = [],
    ) {}

    /**
     * @param  string[]  $scopes
     */
    public function setScopes(array $scopes): void
    {
        $this->scopes = $scopes;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setTestMode(bool $enabled = true): void
    {
        $this->testMode = $enabled;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function setBaseUrl(string $url): void
    {
        $this->baseUrlOverride = rtrim($url, '/');
    }

    public function getBaseUrl(): string
    {
        if ($this->baseUrlOverride !== null) {
            return $this->baseUrlOverride;
        }

        return $this->testMode ? self::TEST_URL : self::BASE_URL;
    }
}
