<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChatBridge\ChatBridgeClient;
use ChatBridge\Exceptions\ChatBridgeException;
use ChatBridge\Exceptions\ValidationException;
use ChatBridge\Exceptions\NotFoundException;
use ChatBridge\Exceptions\RateLimitException;
use ChatBridge\Exceptions\AuthenticationException;

// Initialize client
$apiToken = getenv('CHATBRIDGE_API_TOKEN') ?: 'YOUR_API_TOKEN';
$chatbridge = new ChatBridgeClient($apiToken);

echo "=== Error Handling Examples ===\n\n";

// Example 1: Validation Error (422)
echo "Example 1: Handling Validation Errors\n";
echo "======================================\n";
try {
    $contact = $chatbridge->contacts()->create([
        'phone' => '',  // Empty phone will cause validation error
        'name' => 'Test User'
    ]);
} catch (ValidationException $e) {
    echo "✓ Caught ValidationException\n";
    echo "  Message: {$e->getMessage()}\n";
    echo "  Status Code: {$e->getStatusCode()}\n";
    echo "  Validation Errors:\n";
    foreach ($e->getErrors() as $field => $messages) {
        echo "    - {$field}: " . implode(', ', $messages) . "\n";
    }
}
echo "\n";

// Example 2: Not Found Error (404)
echo "Example 2: Handling Not Found Errors\n";
echo "=====================================\n";
try {
    $contact = $chatbridge->contacts()->get(999999);
} catch (NotFoundException $e) {
    echo "✓ Caught NotFoundException\n";
    echo "  Message: {$e->getMessage()}\n";
    echo "  Status Code: {$e->getStatusCode()}\n";
}
echo "\n";

// Example 3: Rate Limit Error (429)
echo "Example 3: Handling Rate Limit Errors\n";
echo "======================================\n";
echo "Sending many requests to trigger rate limit...\n";
try {
    for ($i = 0; $i < 150; $i++) {
        $chatbridge->contacts()->list(['limit' => 1]);
    }
} catch (RateLimitException $e) {
    echo "✓ Caught RateLimitException\n";
    echo "  Message: {$e->getMessage()}\n";
    echo "  Status Code: {$e->getStatusCode()}\n";
    echo "  Retry After: {$e->getRetryAfter()} seconds\n";
    echo "  Waiting for {$e->getRetryAfter()} seconds...\n";
    sleep($e->getRetryAfter());
    echo "  Ready to retry!\n";
} catch (ChatBridgeException $e) {
    echo "  Other error: {$e->getMessage()}\n";
}
echo "\n";

// Example 4: Authentication Error (401)
echo "Example 4: Handling Authentication Errors\n";
echo "==========================================\n";
try {
    $invalidClient = new ChatBridgeClient('invalid-token-here');
    $invalidClient->contacts()->list();
} catch (AuthenticationException $e) {
    echo "✓ Caught AuthenticationException\n";
    echo "  Message: {$e->getMessage()}\n";
    echo "  Status Code: {$e->getStatusCode()}\n";
    echo "  Action: Check your API token\n";
}
echo "\n";

// Example 5: General Error Handling with Retry Logic
echo "Example 5: Retry Logic for Transient Errors\n";
echo "============================================\n";

function createContactWithRetry($chatbridge, $data, $maxRetries = 3)
{
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        try {
            $attempt++;
            echo "  Attempt {$attempt}...\n";
            
            $contact = $chatbridge->contacts()->create($data);
            echo "  ✓ Success!\n";
            return $contact;
            
        } catch (RateLimitException $e) {
            echo "  ⚠ Rate limit hit, waiting {$e->getRetryAfter()} seconds...\n";
            sleep($e->getRetryAfter());
            
        } catch (ValidationException $e) {
            echo "  ✗ Validation error, cannot retry: {$e->getMessage()}\n";
            throw $e;
            
        } catch (ChatBridgeException $e) {
            if ($e->getStatusCode() >= 500 && $attempt < $maxRetries) {
                echo "  ⚠ Server error, retrying in 2 seconds...\n";
                sleep(2);
            } else {
                echo "  ✗ Error: {$e->getMessage()}\n";
                throw $e;
            }
        }
    }
    
    throw new Exception("Max retries ({$maxRetries}) exceeded");
}

try {
    $contact = createContactWithRetry($chatbridge, [
        'name' => 'Retry Test User',
        'phone' => '6285555555555',
        'email' => 'retry@example.com'
    ]);
    echo "  Contact created: {$contact['name']}\n";
} catch (Exception $e) {
    echo "  Failed to create contact: {$e->getMessage()}\n";
}
echo "\n";

// Example 6: Batch Operations with Error Tracking
echo "Example 6: Batch Operations with Error Tracking\n";
echo "================================================\n";

$contactsToCreate = [
    ['name' => 'User 1', 'phone' => '6286666666666'],
    ['name' => 'User 2', 'phone' => ''],  // Invalid: empty phone
    ['name' => 'User 3', 'phone' => '6287777777777'],
];

$results = [
    'success' => [],
    'validation_errors' => [],
    'other_errors' => []
];

foreach ($contactsToCreate as $data) {
    try {
        $contact = $chatbridge->contacts()->create($data);
        $results['success'][] = $contact;
        echo "  ✓ Created: {$contact['name']}\n";
        
    } catch (ValidationException $e) {
        $results['validation_errors'][] = [
            'data' => $data,
            'error' => $e->getMessage(),
            'errors' => $e->getErrors()
        ];
        echo "  ✗ Validation failed for: {$data['name']}\n";
        
    } catch (ChatBridgeException $e) {
        $results['other_errors'][] = [
            'data' => $data,
            'error' => $e->getMessage()
        ];
        echo "  ✗ Error for: {$data['name']} - {$e->getMessage()}\n";
    }
}

echo "\nBatch Summary:\n";
echo "  Success: " . count($results['success']) . "\n";
echo "  Validation Errors: " . count($results['validation_errors']) . "\n";
echo "  Other Errors: " . count($results['other_errors']) . "\n";

echo "\n=== Error Handling Examples Complete ===\n";
