<?php

namespace ChatBridge\Exceptions;

class NotFoundException extends ChatBridgeException
{
    /**
     * Create a new not found exception.
     *
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(string $message = "Resource not found", int $statusCode = 404)
    {
        parent::__construct($message, $statusCode);
    }
}
