<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChatBridge\ChatBridgeClient;
use ChatBridge\Exceptions\ChatBridgeException;

// Initialize client
$apiToken = getenv('CHATBRIDGE_API_TOKEN') ?: 'YOUR_API_TOKEN';
$chatbridge = new ChatBridgeClient($apiToken);

try {
    echo "=== Send Messages Examples ===\n\n";

    $instanceUuid = 'YOUR_INSTANCE_UUID';  // Replace with your instance UUID

    // 1. Send simple text message
    echo "1. Sending simple text message...\n";
    $result = $chatbridge->whatsapp()->sendMessage($instanceUuid, [
        'message' => 'Hello! This is a test message from ChatBridge PHP SDK.',
        'to' => '6281234567890'  // Replace with actual number
    ]);
    
    echo "  ✓ Message sent successfully!\n";
    echo "  Remaining quota: {$result['data']['message_quota_remaining']}\n\n";

    // 2. Send message with image
    echo "2. Sending message with image...\n";
    $result = $chatbridge->whatsapp()->sendMessage($instanceUuid, [
        'message' => 'Check out our new product!',
        'to' => '6281234567890',
        'file' => __DIR__ . '/assets/product-image.jpg'  // Path to image file
    ]);
    
    echo "  ✓ Image message sent!\n\n";

    // 3. Send message with PDF document
    echo "3. Sending message with PDF...\n";
    $result = $chatbridge->whatsapp()->sendMessage($instanceUuid, [
        'message' => 'Here is our product catalog',
        'to' => '6281234567890',
        'file' => __DIR__ . '/assets/catalog.pdf'  // Path to PDF file
    ]);
    
    echo "  ✓ PDF sent!\n\n";

    // 4. Send message with template
    echo "4. Sending message with template...\n";
    $result = $chatbridge->whatsapp()->sendWithTemplate($instanceUuid, [
        'template_id' => 5,  // Replace with your template ID
        'to' => '6281234567890',
        'parameters' => [
            'name' => 'John Doe',
            'order_id' => 'ORD-12345',
            'amount' => 'Rp 500.000'
        ]
    ]);
    
    echo "  ✓ Template message sent!\n";
    echo "  Remaining quota: {$result['data']['message_quota_remaining']}\n\n";

    // 5. Bulk send to multiple numbers
    echo "5. Sending to multiple recipients...\n";
    $recipients = [
        '6281234567890',
        '6287654321098',
        '6281111111111'
    ];

    $successCount = 0;
    $failedCount = 0;

    foreach ($recipients as $number) {
        try {
            $chatbridge->whatsapp()->sendMessage($instanceUuid, [
                'message' => 'This is a bulk message from ChatBridge',
                'to' => $number
            ]);
            
            $successCount++;
            echo "  ✓ Sent to {$number}\n";
            
            // Rate limiting: wait 100ms between messages
            usleep(100000);
            
        } catch (ChatBridgeException $e) {
            $failedCount++;
            echo "  ✗ Failed to send to {$number}: {$e->getMessage()}\n";
        }
    }

    echo "\n  Summary:\n";
    echo "  - Success: {$successCount}\n";
    echo "  - Failed: {$failedCount}\n\n";

    echo "=== Done ===\n";

} catch (ChatBridgeException $e) {
    echo "Error: {$e->getMessage()}\n";
    echo "Status Code: {$e->getStatusCode()}\n";
}
