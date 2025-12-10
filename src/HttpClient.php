<?php

namespace ChatBridge;

use ChatBridge\Exceptions\ChatBridgeException;
use ChatBridge\Exceptions\ValidationException;
use ChatBridge\Exceptions\NotFoundException;
use ChatBridge\Exceptions\RateLimitException;
use ChatBridge\Exceptions\AuthenticationException;

class HttpClient
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
     * Create a new HTTP client instance.
     *
     * @param string $apiToken
     * @param array $config
     */
    public function __construct(string $apiToken, array $config = [])
    {
        $this->apiToken = $apiToken;
        $this->config = $config;
    }

    /**
     * Send GET request.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws ChatBridgeException
     */
    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->buildUrl($endpoint, $params);
        return $this->request('GET', $url);
    }

    /**
     * Send POST request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $files
     * @return array
     * @throws ChatBridgeException
     */
    public function post(string $endpoint, array $data = [], array $files = []): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('POST', $url, $data, $files);
    }

    /**
     * Send PUT request.
     *
     * @param string $endpoint
     * @param array $data
     * @param array $files
     * @return array
     * @throws ChatBridgeException
     */
    public function put(string $endpoint, array $data = [], array $files = []): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('PUT', $url, $data, $files);
    }

    /**
     * Send DELETE request.
     *
     * @param string $endpoint
     * @return array
     * @throws ChatBridgeException
     */
    public function delete(string $endpoint): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('DELETE', $url);
    }

    /**
     * Send HTTP request.
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $files
     * @return array
     * @throws ChatBridgeException
     */
    protected function request(string $method, string $url, array $data = [], array $files = []): array
    {
        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->apiToken,
            'Accept: application/json',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout'] ?? 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config['connect_timeout'] ?? 10);

        if ($this->config['debug'] ?? false) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            
            if (!empty($files)) {
                // Multipart form data for file uploads
                $postData = $data;
                foreach ($files as $key => $filePath) {
                    if (file_exists($filePath)) {
                        $postData[$key] = new \CURLFile($filePath);
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            } else {
                // JSON data
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            
            if (!empty($files)) {
                // For PUT with files, use multipart with _method
                $data['_method'] = 'PUT';
                $postData = $data;
                foreach ($files as $key => $filePath) {
                    if (file_exists($filePath)) {
                        $postData[$key] = new \CURLFile($filePath);
                    }
                }
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            } else {
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new ChatBridgeException("cURL Error: {$error}");
        }

        return $this->handleResponse($response, $statusCode);
    }

    /**
     * Handle API response.
     *
     * @param string $response
     * @param int $statusCode
     * @return array
     * @throws ChatBridgeException
     */
    protected function handleResponse(string $response, int $statusCode): array
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ChatBridgeException("Invalid JSON response: {$response}");
        }

        // Success responses (2xx)
        if ($statusCode >= 200 && $statusCode < 300) {
            return $data;
        }

        // Error responses
        $message = $data['message'] ?? 'Unknown error';

        switch ($statusCode) {
            case 401:
                throw new AuthenticationException($message, $statusCode);
            case 404:
                throw new NotFoundException($message, $statusCode);
            case 422:
                throw new ValidationException(
                    $message,
                    $statusCode,
                    $data['errors'] ?? []
                );
            case 429:
                throw new RateLimitException(
                    $message,
                    $statusCode,
                    $data['retry_after'] ?? 60
                );
            default:
                throw new ChatBridgeException($message, $statusCode);
        }
    }

    /**
     * Build full URL with query parameters.
     *
     * @param string $endpoint
     * @param array $params
     * @return string
     */
    protected function buildUrl(string $endpoint, array $params = []): string
    {
        $baseUrl = rtrim($this->config['base_url'] ?? 'https://chatbridge.cloud/api/v1', '/');
        $url = $baseUrl . '/' . ltrim($endpoint, '/');

        if (!empty($params)) {
            $query = http_build_query($params);
            $url .= '?' . $query;
        }

        return $url;
    }
}
