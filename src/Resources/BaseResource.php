<?php

namespace ChatBridge\Resources;

use ChatBridge\HttpClient;

abstract class BaseResource
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Create a new resource instance.
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get the resource endpoint.
     *
     * @return string
     */
    abstract protected function getEndpoint(): string;
}
