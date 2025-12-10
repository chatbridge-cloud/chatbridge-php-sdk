<?php

namespace ChatBridge\Exceptions;

use Exception;

class ChatBridgeException extends Exception
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * Create a new ChatBridge exception.
     *
     * @param string $message
     * @param int $statusCode
     * @param Exception|null $previous
     */
    public function __construct(string $message = "", int $statusCode = 0, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
