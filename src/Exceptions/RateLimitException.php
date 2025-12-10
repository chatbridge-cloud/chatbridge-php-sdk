<?php

namespace ChatBridge\Exceptions;

class RateLimitException extends ChatBridgeException
{
    /**
     * @var int
     */
    protected $retryAfter;

    /**
     * Create a new rate limit exception.
     *
     * @param string $message
     * @param int $statusCode
     * @param int $retryAfter
     */
    public function __construct(string $message = "Rate limit exceeded", int $statusCode = 429, int $retryAfter = 60)
    {
        parent::__construct($message, $statusCode);
        $this->retryAfter = $retryAfter;
    }

    /**
     * Get retry after seconds.
     *
     * @return int
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
