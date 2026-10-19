<?php

namespace Aqtivite\Php\Exceptions;

class MissingScopeException extends ApiException
{
    /**
     * @param  string[]  $scopes
     */
    public function __construct(
        public readonly array $scopes,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: 'The access token is missing the required scope(s): ' . implode(' ', $scopes) . '. Grant them to the client on the Aqtivite API, or include them in the requested scopes.',
            code: 403,
            errorType: 'insufficient_scope',
            previous: $previous,
        );
    }
}
