<?php

namespace Aqtivite\Php\Resources\User;

use Aqtivite\Php\Resources\Resource;
use Aqtivite\Php\Response\ApiResponse;

class PostResource extends Resource
{
    protected string $basePath = '/user/posts';

    public function create(array $data): ApiResponse
    {
        $multipart = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $multipart[] = ['name' => "{$key}[]", 'contents' => $item];
                }
            } else {
                $multipart[] = ['name' => $key, 'contents' => $value];
            }
        }

        return $this->http->post($this->basePath, $multipart, 'multipart');
    }
}
