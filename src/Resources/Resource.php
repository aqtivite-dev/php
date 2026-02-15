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

    public function get(array $filter = [], array $query = []): ApiResponse
    {
        $params = [];

        foreach ($filter as $key => $value) {
            $params["filter[{$key}]"] = $value;
        }

        foreach ($query as $key => $value) {
            $params[$key] = $value;
        }

        return $this->http->get($this->basePath, $params);
    }

    public function find(string|int $id): ApiResponse
    {
        return $this->http->get("{$this->basePath}/{$id}");
    }
}
