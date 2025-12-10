# ChatBridge PHP SDK

Official PHP SDK untuk ChatBridge WhatsApp Broadcasting API.

## Requirements

- PHP 7.4 atau lebih tinggi
- Extension: `json`, `curl`

## Instalasi

### Via Composer (Recommended)

```bash
composer require chatbridge-cloud/chatbridge-php-sdk
```

### Manual Installation

Download repository ini dan include file autoload:

```php
require_once 'path/to/sdk/php/vendor/autoload.php';
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use ChatBridge\ChatBridgeClient;

// Inisialisasi client
$chatbridge = new ChatBridgeClient('YOUR_API_TOKEN');

// List WhatsApp instances
$instances = $chatbridge->whatsapp()->list();

foreach ($instances as $instance) {
    echo "Instance: {$instance['instance_uuid']}\n";
    echo "Status: {$instance['status']}\n";
}

// Kirim pesan
$result = $chatbridge->whatsapp()->sendMessage(
    'instance-uuid-here',
    [
        'message' => 'Halo dari ChatBridge PHP SDK!',
        'to' => '6281234567890'
    ]
);

if ($result['success']) {
    echo "Pesan terkirim!\n";
}
```

## Fitur Lengkap

### 1. WhatsApp Instance Management

```php
// List semua instance
$instances = $chatbridge->whatsapp()->list(['status' => 'authenticated']);

// Get detail instance
$instance = $chatbridge->whatsapp()->get('instance-uuid');

// Create instance baru
$result = $chatbridge->whatsapp()->create();

// Get QR code
$qr = $chatbridge->whatsapp()->getQrCode('instance-uuid');
echo "QR URL: {$qr['qr_url']}\n";

// Kirim pesan
$result = $chatbridge->whatsapp()->sendMessage('instance-uuid', [
    'message' => 'Hello World',
    'to' => '6281234567890'
]);

// Kirim pesan dengan file
$result = $chatbridge->whatsapp()->sendMessage('instance-uuid', [
    'message' => 'Berikut adalah catalog kami',
    'to' => '6281234567890',
    'file' => '/path/to/catalog.pdf'
]);

// Kirim dengan template
$result = $chatbridge->whatsapp()->sendWithTemplate('instance-uuid', [
    'template_id' => 5,
    'to' => '6281234567890',
    'parameters' => [
        'name' => 'Budi',
        'order_id' => 'ORD-12345'
    ]
]);
```

### 2. Template Management

```php
// List templates
$templates = $chatbridge->templates()->list([
    'type' => 'text',
    'search' => 'promo'
]);

// Get template detail
$template = $chatbridge->templates()->get(5);

// Create template
$template = $chatbridge->templates()->create([
    'name' => 'Welcome Message',
    'content' => 'Halo {name}, selamat datang di {company}!',
    'type' => 'text',
    'is_active' => true
]);

// Create template dengan file
$template = $chatbridge->templates()->create([
    'name' => 'Product Catalog',
    'content' => 'Katalog produk terbaru',
    'type' => 'image',
    'file' => '/path/to/image.jpg'
]);

// Update template
$template = $chatbridge->templates()->update(5, [
    'name' => 'Welcome Message Updated'
]);

// Delete template
$chatbridge->templates()->delete(5);
```

### 3. Audience Management

```php
// List audiences
$audiences = $chatbridge->audiences()->list([
    'search' => 'VIP',
    'is_active' => true
]);

// Get audience detail (dengan contacts)
$audience = $chatbridge->audiences()->get(10, ['with_contacts' => true]);

// Create audience
$audience = $chatbridge->audiences()->create([
    'name' => 'VIP Customers',
    'description' => 'High value customers',
    'tag' => 'vip',
    'contacts' => [
        ['phone' => '6281234567890', 'name' => 'John Doe'],
        ['phone' => '6287654321098', 'name' => 'Jane Smith']
    ]
]);

// Update audience
$audience = $chatbridge->audiences()->update(10, [
    'name' => 'VIP Premium Customers',
    'contacts' => [
        ['phone' => '6281111111111', 'name' => 'New Contact']
    ]
]);

// Delete audience
$chatbridge->audiences()->delete(10);
```

### 4. Contact Management

```php
// List contacts
$contacts = $chatbridge->contacts()->list([
    'search' => 'john',
    'is_active' => true,
    'limit' => 50,
    'offset' => 0
]);

// Get contact detail
$contact = $chatbridge->contacts()->get(1);

// Create contact
$contact = $chatbridge->contacts()->create([
    'name' => 'Ahmad Wijaya',
    'phone' => '6281234567890',
    'email' => 'ahmad@example.com',
    'custom_fields' => [
        'company' => 'PT Maju Jaya',
        'position' => 'CEO'
    ]
]);

// Update contact
$contact = $chatbridge->contacts()->update(1, [
    'name' => 'Ahmad Wijaya Updated',
    'email' => 'ahmad.updated@example.com'
]);

// Delete contact
$chatbridge->contacts()->delete(1);
```

### 5. Campaign Management

```php
// List campaigns
$campaigns = $chatbridge->campaigns()->list([
    'status' => 'completed',
    'search' => 'promo'
]);

// Get campaign detail
$campaign = $chatbridge->campaigns()->get(5);

// Create campaign (scheduled)
$campaign = $chatbridge->campaigns()->create([
    'name' => 'Flash Sale Campaign',
    'audience_id' => 10,
    'template_id' => 5,
    'whatsapp_instance_id' => 'instance-uuid',
    'scheduled_at' => '2025-12-25 10:00:00',
    'send_immediately' => false
]);

// Create campaign (send immediately)
$campaign = $chatbridge->campaigns()->create([
    'name' => 'Urgent Announcement',
    'audience_id' => 10,
    'template_id' => 5,
    'whatsapp_instance_id' => 'instance-uuid',
    'send_immediately' => true
]);

// Update campaign
$campaign = $chatbridge->campaigns()->update(5, [
    'name' => 'Updated Campaign Name'
]);

// Delete campaign
$chatbridge->campaigns()->delete(5);
```

## Error Handling

```php
use ChatBridge\Exceptions\ChatBridgeException;
use ChatBridge\Exceptions\ValidationException;
use ChatBridge\Exceptions\NotFoundException;
use ChatBridge\Exceptions\RateLimitException;

try {
    $contact = $chatbridge->contacts()->create([
        'phone' => '6281234567890',
        'name' => 'John Doe'
    ]);
} catch (ValidationException $e) {
    // Validation error (422)
    echo "Validation Error: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
} catch (NotFoundException $e) {
    // Not found (404)
    echo "Not Found: " . $e->getMessage() . "\n";
} catch (RateLimitException $e) {
    // Rate limit exceeded (429)
    echo "Rate Limit: " . $e->getMessage() . "\n";
    echo "Retry after: " . $e->getRetryAfter() . " seconds\n";
} catch (ChatBridgeException $e) {
    // General API error
    echo "Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getStatusCode() . "\n";
}
```

## Advanced Usage

### Custom Base URL

```php
$chatbridge = new ChatBridgeClient('YOUR_API_TOKEN', [
    'base_url' => 'https://custom-api.chatbridge.cloud/api/v1'
]);
```

### Timeout Configuration

```php
$chatbridge = new ChatBridgeClient('YOUR_API_TOKEN', [
    'timeout' => 60,  // 60 seconds
    'connect_timeout' => 10  // 10 seconds
]);
```

### Debug Mode

```php
$chatbridge = new ChatBridgeClient('YOUR_API_TOKEN', [
    'debug' => true  // Enable debug output
]);
```

### Retry Configuration

```php
$chatbridge = new ChatBridgeClient('YOUR_API_TOKEN', [
    'max_retries' => 3,
    'retry_delay' => 1000  // milliseconds
]);
```

## Batch Operations

### Import Multiple Contacts

```php
$contacts = [
    ['name' => 'John Doe', 'phone' => '6281234567890'],
    ['name' => 'Jane Smith', 'phone' => '6287654321098'],
    ['name' => 'Bob Johnson', 'phone' => '6281111111111']
];

$results = [
    'success' => [],
    'failed' => []
];

foreach ($contacts as $contactData) {
    try {
        $contact = $chatbridge->contacts()->create($contactData);
        $results['success'][] = $contact;
    } catch (ChatBridgeException $e) {
        $results['failed'][] = [
            'data' => $contactData,
            'error' => $e->getMessage()
        ];
    }
    
    // Rate limiting: wait 100ms
    usleep(100000);
}

echo "Success: " . count($results['success']) . "\n";
echo "Failed: " . count($results['failed']) . "\n";
```

## Response Format

Semua method mengembalikan array dengan struktur:

```php
[
    'success' => true,
    'message' => 'Operation successful',
    'data' => [...]  // Response data
]
```

## Testing

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage
```

## Examples

Lihat folder `examples/` untuk contoh penggunaan lengkap:

- `examples/whatsapp-basic.php` - WhatsApp instance management
- `examples/send-message.php` - Kirim pesan
- `examples/campaign-workflow.php` - Campaign workflow lengkap
- `examples/bulk-import.php` - Import contacts dalam jumlah besar

## Support

- Email: support@chatbridge.cloud
- Documentation: https://chatbridge.cloud/docs
- GitHub Issues: https://github.com/chatbridge/php-sdk/issues

## License

MIT License. See LICENSE file for details.
