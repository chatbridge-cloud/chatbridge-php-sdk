<?php

namespace ChatBridge\Resources;

class AudienceResource extends BaseResource
{
    /**
     * Get the resource endpoint.
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'audiences';
    }

    /**
     * List all audiences.
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
     * Get an audience by ID.
     *
     * @param int $id
     * @param array $params
     * @return array
     */
    public function get(int $id, array $params = []): array
    {
        $response = $this->httpClient->get("{$this->getEndpoint()}/{$id}", $params);
        return $response['data'] ?? [];
    }

    /**
     * Create a new audience.
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $response = $this->httpClient->post($this->getEndpoint(), $data);
        return $response['data'] ?? [];
    }

    /**
     * Update an audience.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        $response = $this->httpClient->put("{$this->getEndpoint()}/{$id}", $data);
        return $response['data'] ?? [];
    }

    /**
     * Delete an audience.
     *
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        return $this->httpClient->delete("{$this->getEndpoint()}/{$id}");
    }
}
