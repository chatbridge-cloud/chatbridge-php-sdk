<?php

namespace ChatBridge\Resources;

class WhatsAppResource extends BaseResource
{
    /**
     * Get the resource endpoint.
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'whatsapp';
    }

    /**
     * List all WhatsApp instances.
     *
     * @param array $params
     * @return array
     */
    public function list(array $params = []): array
    {
        $response = $this->httpClient->get($this->getEndpoint(), $params);
        return $response['data'] ?? [];
    }

    /**
     * Get a WhatsApp instance by UUID.
     *
     * @param string $instanceUuid
     * @return array
     */
    public function get(string $instanceUuid): array
    {
        $response = $this->httpClient->get("{$this->getEndpoint()}/{$instanceUuid}");
        return $response['data'] ?? [];
    }

    /**
     * Create a new WhatsApp instance.
     *
     * @return array
     */
    public function create(): array
    {
        $response = $this->httpClient->post($this->getEndpoint());
        return $response;
    }

    /**
     * Get QR code for an instance.
     *
     * @param string $instanceUuid
     * @return array
     */
    public function getQrCode(string $instanceUuid): array
    {
        $response = $this->httpClient->get("{$this->getEndpoint()}/{$instanceUuid}/qrcode");
        return $response['data'] ?? [];
    }

    /**
     * Send a message.
     *
     * @param string $instanceUuid
     * @param array $data
     * @return array
     */
    public function sendMessage(string $instanceUuid, array $data): array
    {
        $files = [];
        
        if (isset($data['file'])) {
            $files['file'] = $data['file'];
            unset($data['file']);
        }

        $response = $this->httpClient->post(
            "{$this->getEndpoint()}/{$instanceUuid}/send-message",
            $data,
            $files
        );
        
        return $response;
    }

    /**
     * Send a message with template.
     *
     * @param string $instanceUuid
     * @param array $data
     * @return array
     */
    public function sendWithTemplate(string $instanceUuid, array $data): array
    {
        $response = $this->httpClient->post(
            "{$this->getEndpoint()}/{$instanceUuid}/send-template",
            $data
        );
        
        return $response;
    }
}
