<?php

namespace Aqtivite\Php\Resources\User;

use Aqtivite\Php\Resources\Resource;
use Aqtivite\Php\Response\ApiResponse;

class UserResource extends Resource
{
    protected string $basePath = '/user/users';

    public function events(string $slug, array $filter = []): ApiResponse
    {
        $query = [];

        foreach ($filter as $key => $value) {
            $query["filter[{$key}]"] = $value;
        }

        return $this->http->get("{$this->basePath}/{$slug}/events", $query);
    }
}
