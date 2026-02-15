<?php

namespace Aqtivite\Php\Response;

class ApiResponse
{
    public function __construct(
        public readonly bool $status,
        public readonly mixed $data = null,
        public readonly ?array $error = null,
        public readonly ?float $elapsedTime = null,
        public readonly array $raw = [],
    ) {}

    public static function fromArray(array $response): static
    {
        if (isset($response['paginate'])) {
            return PaginatedResponse::fromArray($response);
        }

        return new static(
            status: $response['status'] ?? false,
            data: $response['data'] ?? null,
            error: $response['error'] ?? null,
            elapsedTime: $response['elapsed_time'] ?? null,
            raw: $response,
        );
    }

    public function successful(): bool
    {
        return $this->status;
    }

    public function failed(): bool
    {
        return !$this->status;
    }

    public function errorCode(): ?int
    {
        return $this->error['code'] ?? null;
    }

    public function errorDescription(): ?string
    {
        return $this->error['description'] ?? null;
    }

    public function errorType(): ?string
    {
        return $this->error['type'] ?? null;
    }
}
