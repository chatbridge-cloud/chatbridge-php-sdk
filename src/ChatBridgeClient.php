<?php

namespace ChatBridge;

use ChatBridge\Resources\WhatsAppResource;
use ChatBridge\Resources\TemplateResource;
use ChatBridge\Resources\AudienceResource;
use ChatBridge\Resources\ContactResource;
use ChatBridge\Resources\CampaignResource;

class ChatBridgeClient
{
    /**
     * @var string
     */
    protected $apiToken;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Create a new ChatBridge client instance.
     *
     * @param string $apiToken
     * @param array $config
     */
    public function __construct(string $apiToken, array $config = [])
    {
        $this->apiToken = $apiToken;
        $this->config = array_merge([
            'base_url' => 'https://chatbridge.cloud/api/v1',
            'timeout' => 30,
            'connect_timeout' => 10,
            'debug' => false,
            'max_retries' => 2,
            'retry_delay' => 1000,
        ], $config);

        $this->httpClient = new HttpClient($this->apiToken, $this->config);
    }

    /**
     * Get WhatsApp resource instance.
     *
     * @return WhatsAppResource
     */
    public function whatsapp(): WhatsAppResource
    {
        return new WhatsAppResource($this->httpClient);
    }

    /**
     * Get Template resource instance.
     *
     * @return TemplateResource
     */
    public function templates(): TemplateResource
    {
        return new TemplateResource($this->httpClient);
    }

    /**
     * Get Audience resource instance.
     *
     * @return AudienceResource
     */
    public function audiences(): AudienceResource
    {
        return new AudienceResource($this->httpClient);
    }

    /**
     * Get Contact resource instance.
     *
     * @return ContactResource
     */
    public function contacts(): ContactResource
    {
        return new ContactResource($this->httpClient);
    }

    /**
     * Get Campaign resource instance.
     *
     * @return CampaignResource
     */
    public function campaigns(): CampaignResource
    {
        return new CampaignResource($this->httpClient);
    }

    /**
     * Get the API token.
     *
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    /**
     * Get configuration value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get the HTTP client instance.
     *
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }
}
