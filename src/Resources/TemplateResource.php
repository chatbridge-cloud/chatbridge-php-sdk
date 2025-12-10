<?php

namespace ChatBridge\Resources;

class TemplateResource extends BaseResource
{
    /**
     * Get the resource endpoint.
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'templates';
    }

    /**
     * List all templates.
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
     * Get a template by ID.
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
     * Create a new template.
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $files = [];
        
        if (isset($data['file'])) {
            $files['file'] = $data['file'];
            unset($data['file']);
        }

        $response = $this->httpClient->post($this->getEndpoint(), $data, $files);
        return $response['data'] ?? [];
    }

    /**
     * Update a template.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        $files = [];
        
        if (isset($data['file'])) {
            $files['file'] = $data['file'];
            unset($data['file']);
        }

        $response = $this->httpClient->put("{$this->getEndpoint()}/{$id}", $data, $files);
        return $response['data'] ?? [];
    }

    /**
     * Delete a template.
     *
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        return $this->httpClient->delete("{$this->getEndpoint()}/{$id}");
    }
}
