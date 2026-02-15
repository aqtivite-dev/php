<?php

namespace Aqtivite\Php\Resources\User;

use Aqtivite\Php\Resources\Resource;
use Aqtivite\Php\Response\ApiResponse;

class UserResource extends Resource
{
    protected string $basePath = '/user/users';

    public function events(string $slug, array $filter = [], array $query = []): ApiResponse
    {
        $params = [];

        foreach ($filter as $key => $value) {
            $params["filter[{$key}]"] = $value;
        }

        foreach ($query as $key => $value) {
            $params[$key] = $value;
        }

        return $this->http->get("{$this->basePath}/{$slug}/events", $params);
    }
}
