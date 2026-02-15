<?php

namespace Aqtivite\Php\Resources;

use Aqtivite\Php\HttpClient;
use Aqtivite\Php\Response\ApiResponse;

abstract class Resource
{
    protected string $basePath;

    public function __construct(
        protected readonly HttpClient $http,
    ) {}

    public function get(array $filter = []): ApiResponse
    {
        $query = [];

        foreach ($filter as $key => $value) {
            $query["filter[{$key}]"] = $value;
        }

        return $this->http->get($this->basePath, $query);
    }

    public function find(string|int $id): ApiResponse
    {
        return $this->http->get("{$this->basePath}/{$id}");
    }
}
