<?php

namespace Aqtivite\Php\Response;

class PaginatedResponse extends ApiResponse
{
    public function __construct(
        bool $status,
        mixed $data = null,
        ?array $error = null,
        ?float $elapsedTime = null,
        array $raw = [],
        public readonly ?int $from = null,
        public readonly ?int $to = null,
        public readonly ?int $total = null,
        public readonly ?int $currentPage = null,
        public readonly ?int $lastPage = null,
        public readonly ?int $nextPage = null,
        public readonly ?int $previousPage = null,
        public readonly ?int $firstPage = null,
    ) {
        parent::__construct($status, $data, $error, $elapsedTime, $raw);
    }

    public static function fromArray(array $response): static
    {
        $paginate = $response['paginate'] ?? [];

        return new static(
            status: $response['status'] ?? false,
            data: $response['data'] ?? null,
            error: $response['error'] ?? null,
            elapsedTime: $response['elapsed_time'] ?? null,
            raw: $response,
            from: $response['from'] ?? null,
            to: $response['to'] ?? null,
            total: $response['total'] ?? null,
            currentPage: $paginate['current'] ?? null,
            lastPage: $paginate['last'] ?? null,
            nextPage: $paginate['next'] ?? null,
            previousPage: $paginate['previous'] ?? null,
            firstPage: $paginate['first'] ?? null,
        );
    }

    public function hasNextPage(): bool
    {
        return $this->nextPage !== null;
    }

    public function hasPreviousPage(): bool
    {
        return $this->previousPage !== null;
    }
}
