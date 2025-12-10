<?php

namespace ChatBridge\Resources;

class ContactResource extends BaseResource
{
    /**
     * Get the resource endpoint.
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'contacts';
    }

    /**
     * List all contacts.
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
     * Get a contact by ID.
     *
     * @param int $id
     * @return array
     */
    public function get(int $id): array
    {
        $response = $this->httpClient->get("{$this->getEndpoint()}/{$id}");
        return $response['data'] ?? [];
    }

    /**
     * Create a new contact.
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
     * Update a contact.
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
     * Delete a contact.
     *
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        return $this->httpClient->delete("{$this->getEndpoint()}/{$id}");
    }
}
