<?php

namespace ChatBridge\Exceptions;

class ValidationException extends ChatBridgeException
{
    /**
     * @var array
     */
    protected $errors;

    /**
     * Create a new validation exception.
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     */
    public function __construct(string $message = "", int $statusCode = 422, array $errors = [])
    {
        parent::__construct($message, $statusCode);
        $this->errors = $errors;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
