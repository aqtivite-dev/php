<?php

namespace Aqtivite\Php\Auth;

class PasswordCredential implements CredentialInterface
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
    ) {}

    public function getGrantType(): string
    {
        return 'password';
    }

    public function toRequestBody(string $clientId, string $clientSecret): array
    {
        return [
            'grant_type' => $this->getGrantType(),
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'username' => $this->username,
            'password' => $this->password,
            'scope' => '',
        ];
    }
}
