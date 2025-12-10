<?php

namespace ChatBridge\Exceptions;

class AuthenticationException extends ChatBridgeException
{
    /**
     * Create a new authentication exception.
     *
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(string $message = "Authentication failed", int $statusCode = 401)
    {
        parent::__construct($message, $statusCode);
    }
}
