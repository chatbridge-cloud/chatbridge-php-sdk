<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChatBridge\ChatBridgeClient;
use ChatBridge\Exceptions\ChatBridgeException;
use ChatBridge\Exceptions\ValidationException;

// Initialize client
$apiToken = getenv('CHATBRIDGE_API_TOKEN') ?: 'YOUR_API_TOKEN';
$chatbridge = new ChatBridgeClient($apiToken);

try {
    echo "=== Complete Campaign Workflow ===\n\n";

    // Step 1: Create contacts
    echo "Step 1: Creating contacts...\n";
    $contactsData = [
        ['name' => 'John Doe', 'phone' => '6281234567890', 'email' => 'john@example.com'],
        ['name' => 'Jane Smith', 'phone' => '6287654321098', 'email' => 'jane@example.com'],
        ['name' => 'Bob Johnson', 'phone' => '6281111111111', 'email' => 'bob@example.com']
    ];

    $createdContacts = [];
    foreach ($contactsData as $data) {
        try {
            $contact = $chatbridge->contacts()->create($data);
            $createdContacts[] = $contact;
            echo "  ✓ Created: {$contact['name']} ({$contact['phone']})\n";
        } catch (ValidationException $e) {
            echo "  ✗ Failed: {$data['name']} - {$e->getMessage()}\n";
        }
    }
    echo "\n";

    // Step 2: Create audience with contacts
    echo "Step 2: Creating audience with contacts...\n";
    $audience = $chatbridge->audiences()->create([
        'name' => 'VIP Customers December 2025',
        'description' => 'High value customers for year-end campaign',
        'tag' => 'vip-dec-2025',
        'contacts' => array_map(function($contact) {
            return [
                'phone' => $contact['phone'],
                'name' => $contact['name']
            ];
        }, $createdContacts)
    ]);
    
    echo "  ✓ Audience created: {$audience['name']}\n";
    echo "  ID: {$audience['id']}\n";
    echo "  Total contacts: {$audience['contacts_count']}\n\n";

    // Step 3: Create message template
    echo "Step 3: Creating message template...\n";
    $template = $chatbridge->templates()->create([
        'name' => 'Year End Promo 2025',
        'content' => 'Hi {name}! 🎉 Special year-end discount {discount}% for you. Valid until Dec 31. Order now!',
        'type' => 'text',
        'is_active' => true
    ]);
    
    echo "  ✓ Template created: {$template['name']}\n";
    echo "  ID: {$template['id']}\n\n";

    // Step 4: Get WhatsApp instance
    echo "Step 4: Getting WhatsApp instance...\n";
    $instances = $chatbridge->whatsapp()->list(['status' => 'authenticated']);
    
    if (empty($instances)) {
        echo "  ✗ No authenticated WhatsApp instance found\n";
        echo "  Please create and authenticate an instance first\n";
        exit(1);
    }
    
    $instance = $instances[0];
    echo "  ✓ Using instance: {$instance['instance_uuid']}\n";
    echo "  Number: {$instance['logged_number']}\n\n";

    // Step 5: Create campaign
    echo "Step 5: Creating campaign...\n";
    $campaign = $chatbridge->campaigns()->create([
        'name' => 'Year End Campaign 2025',
        'audience_id' => $audience['id'],
        'template_id' => $template['id'],
        'whatsapp_instance_id' => $instance['instance_uuid'],
        'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        'send_immediately' => false
    ]);
    
    echo "  ✓ Campaign created: {$campaign['name']}\n";
    echo "  ID: {$campaign['id']}\n";
    echo "  Status: {$campaign['status']}\n";
    echo "  Scheduled: {$campaign['scheduled_at']}\n\n";

    // Step 6: Get campaign details
    echo "Step 6: Getting campaign details...\n";
    $campaignDetails = $chatbridge->campaigns()->get($campaign['id']);
    
    echo "  Campaign: {$campaignDetails['name']}\n";
    echo "  Audience: {$campaignDetails['audience']['name']}\n";
    echo "  Template: {$campaignDetails['template']['name']}\n";
    echo "  Total Recipients: {$campaignDetails['audience']['contacts_count']}\n\n";

    // Optional: Update campaign
    echo "Step 7: Updating campaign schedule...\n";
    $updatedCampaign = $chatbridge->campaigns()->update($campaign['id'], [
        'scheduled_at' => date('Y-m-d H:i:s', strtotime('+2 hours'))
    ]);
    
    echo "  ✓ Campaign rescheduled to: {$updatedCampaign['scheduled_at']}\n\n";

    echo "=== Campaign Workflow Complete ===\n\n";
    echo "Summary:\n";
    echo "  - Contacts created: " . count($createdContacts) . "\n";
    echo "  - Audience ID: {$audience['id']}\n";
    echo "  - Template ID: {$template['id']}\n";
    echo "  - Campaign ID: {$campaign['id']}\n";
    echo "  - Scheduled at: {$updatedCampaign['scheduled_at']}\n";

} catch (ChatBridgeException $e) {
    echo "Error: {$e->getMessage()}\n";
    echo "Status Code: {$e->getStatusCode()}\n";
}
