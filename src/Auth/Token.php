<?php

namespace Aqtivite\Php\Auth;

class Token
{
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?string $tokenType = 'Bearer',
        public readonly ?int $expiresIn = null,
    ) {}
}
