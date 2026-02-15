<?php

namespace Aqtivite\Php\Auth;

class ApiCredential implements CredentialInterface
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $apiSecret,
    ) {}

    public function getGrantType(): string
    {
        return 'client_credentials';
    }

    public function toRequestBody(string $clientId, string $clientSecret): array
    {
        return [
            'grant_type' => $this->getGrantType(),
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'scope' => '',
        ];
    }
}
