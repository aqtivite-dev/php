<?php

namespace Aqtivite\Php\Auth;

use DateTimeImmutable;

class Token
{
    /**
     * @param  string[]  $scopes
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly ?DateTimeImmutable $expiresAt = null,
        public readonly string $tokenType = 'Bearer',
        public readonly array $scopes = [],
    ) {}

    public static function fromResponse(array $body): static
    {
        return new static(
            accessToken: $body['access_token'],
            expiresAt: isset($body['expires_in']) ? new DateTimeImmutable('+' . (int) $body['expires_in'] . ' seconds') : null,
            tokenType: $body['token_type'] ?? 'Bearer',
            scopes: array_values(array_filter(explode(' ', $body['scope'] ?? ''))),
        );
    }

    public function isExpired(int $leeway = 60): bool
    {
        return $this->expiresAt !== null && $this->expiresAt->getTimestamp() - $leeway <= time();
    }
}
