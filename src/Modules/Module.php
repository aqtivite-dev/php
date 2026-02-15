<?php

namespace Aqtivite\Php\Modules;

use Aqtivite\Php\HttpClient;

abstract class Module
{
    public function __construct(
        protected readonly HttpClient $http,
    ) {}
}
