<?php

namespace Aqtivite\Php\Concerns;

use BadMethodCallException;
use Closure;

trait Macroable
{
    private static array $macros = [];

    public static function macro(string $name, Closure $callback): void
    {
        static::$macros[$name] = $callback;
    }

    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    public static function flushMacros(): void
    {
        static::$macros = [];
    }

    public function __call(string $method, array $args): mixed
    {
        if (!static::hasMacro($method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        return static::$macros[$method]->bindTo($this, static::class)(...$args);
    }
}
