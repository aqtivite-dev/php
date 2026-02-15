<?php

namespace Aqtivite\Php\Auth;

interface CredentialInterface
{
    public function toRequestBody(string $clientId, string $clientSecret): array;

    public function getGrantType(): string;
}
