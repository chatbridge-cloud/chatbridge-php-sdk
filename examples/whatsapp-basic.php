<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChatBridge\ChatBridgeClient;
use ChatBridge\Exceptions\ChatBridgeException;

// Initialize client
$apiToken = getenv('CHATBRIDGE_API_TOKEN') ?: 'YOUR_API_TOKEN';
$chatbridge = new ChatBridgeClient($apiToken);

try {
    echo "=== WhatsApp Instance Management ===\n\n";

    // 1. List all instances
    echo "1. Listing all WhatsApp instances...\n";
    $instances = $chatbridge->whatsapp()->list();
    
    foreach ($instances as $instance) {
        echo "  - UUID: {$instance['instance_uuid']}\n";
        echo "    Status: {$instance['status']}\n";
        echo "    Number: {$instance['logged_number']}\n\n";
    }

    // 2. Create new instance
    echo "2. Creating new WhatsApp instance...\n";
    $result = $chatbridge->whatsapp()->create();
    echo "  Result: {$result['message']}\n\n";

    // Wait a bit for instance to initialize
    echo "  Waiting 5 seconds for instance to initialize...\n";
    sleep(5);

    // 3. Get updated instance list
    echo "3. Getting updated instance list...\n";
    $instances = $chatbridge->whatsapp()->list();
    
    if (!empty($instances)) {
        $latestInstance = $instances[0];
        $instanceUuid = $latestInstance['instance_uuid'];
        
        echo "  Latest instance UUID: {$instanceUuid}\n";
        echo "  Status: {$latestInstance['status']}\n\n";

        // 4. Get instance detail
        echo "4. Getting instance detail...\n";
        $instance = $chatbridge->whatsapp()->get($instanceUuid);
        echo "  UUID: {$instance['instance_uuid']}\n";
        echo "  Status: {$instance['status']}\n";
        echo "  Auto Response: " . ($instance['activate_autoresponse'] ? 'Yes' : 'No') . "\n\n";

        // 5. Get QR code if status is qr_ready
        if ($instance['status'] === 'qr_ready') {
            echo "5. Getting QR code...\n";
            $qrData = $chatbridge->whatsapp()->getQrCode($instanceUuid);
            echo "  QR URL: {$qrData['qr_url']}\n";
            echo "  Please scan this QR code with WhatsApp mobile app\n\n";
        } else {
            echo "5. Instance status is '{$instance['status']}', QR code not available\n\n";
        }

        // 6. Send message (if authenticated)
        if ($instance['status'] === 'authenticated' || $instance['status'] === 'ready') {
            echo "6. Sending test message...\n";
            
            $messageResult = $chatbridge->whatsapp()->sendMessage($instanceUuid, [
                'message' => 'Hello from ChatBridge PHP SDK!',
                'to' => '6281234567890'  // Replace with actual number
            ]);
            
            echo "  Message sent successfully!\n";
            echo "  Storage used: {$messageResult['data']['storage_used']} bytes\n";
            echo "  Remaining quota: {$messageResult['data']['message_quota_remaining']}\n\n";
        } else {
            echo "6. Cannot send message, instance not authenticated yet\n\n";
        }
    }

    echo "=== Done ===\n";

} catch (ChatBridgeException $e) {
    echo "Error: {$e->getMessage()}\n";
    echo "Status Code: {$e->getStatusCode()}\n";
}
