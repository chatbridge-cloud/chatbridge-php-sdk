<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChatBridge\ChatBridgeClient;
use ChatBridge\Exceptions\ChatBridgeException;
use ChatBridge\Exceptions\ValidationException;

// Initialize client
$apiToken = getenv('CHATBRIDGE_API_TOKEN') ?: 'YOUR_API_TOKEN';
$chatbridge = new ChatBridgeClient($apiToken);

/**
 * Import contacts from CSV file
 */
function importFromCSV($chatbridge, $csvFile)
{
    echo "=== Importing Contacts from CSV ===\n\n";
    
    if (!file_exists($csvFile)) {
        echo "Error: CSV file not found: {$csvFile}\n";
        return;
    }

    $results = [
        'success' => [],
        'failed' => [],
        'duplicates' => []
    ];

    $handle = fopen($csvFile, 'r');
    $header = fgetcsv($handle); // Skip header row

    echo "Reading contacts from CSV...\n";
    
    $rowNumber = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;
        
        $contactData = [
            'name' => $row[0] ?? '',
            'phone' => $row[1] ?? '',
            'email' => $row[2] ?? '',
            'custom_fields' => [
                'company' => $row[3] ?? '',
                'position' => $row[4] ?? ''
            ]
        ];

        // Skip if phone is empty
        if (empty($contactData['phone'])) {
            echo "  Row {$rowNumber}: Skipped (empty phone)\n";
            continue;
        }

        try {
            $contact = $chatbridge->contacts()->create($contactData);
            $results['success'][] = $contact;
            echo "  Row {$rowNumber}: ✓ {$contact['name']} ({$contact['phone']})\n";
            
            // Rate limiting: wait 100ms between requests
            usleep(100000);
            
        } catch (ValidationException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                $results['duplicates'][] = $contactData;
                echo "  Row {$rowNumber}: ⚠ Duplicate - {$contactData['phone']}\n";
            } else {
                $results['failed'][] = [
                    'data' => $contactData,
                    'error' => $e->getMessage()
                ];
                echo "  Row {$rowNumber}: ✗ Validation error - {$e->getMessage()}\n";
            }
        } catch (ChatBridgeException $e) {
            $results['failed'][] = [
                'data' => $contactData,
                'error' => $e->getMessage()
            ];
            echo "  Row {$rowNumber}: ✗ Error - {$e->getMessage()}\n";
        }
    }

    fclose($handle);

    echo "\n=== Import Complete ===\n";
    echo "Success: " . count($results['success']) . "\n";
    echo "Duplicates: " . count($results['duplicates']) . "\n";
    echo "Failed: " . count($results['failed']) . "\n\n";

    return $results;
}

/**
 * Import contacts from array
 */
function importFromArray($chatbridge, $contactsArray)
{
    echo "=== Bulk Import Contacts ===\n\n";
    
    $results = [
        'success' => [],
        'failed' => []
    ];

    $total = count($contactsArray);
    echo "Importing {$total} contacts...\n\n";

    foreach ($contactsArray as $index => $contactData) {
        $position = $index + 1;
        
        try {
            $contact = $chatbridge->contacts()->create($contactData);
            $results['success'][] = $contact;
            
            echo "  [{$position}/{$total}] ✓ {$contact['name']} ({$contact['phone']})\n";
            
            // Rate limiting
            usleep(100000);
            
        } catch (ChatBridgeException $e) {
            $results['failed'][] = [
                'data' => $contactData,
                'error' => $e->getMessage()
            ];
            
            $name = $contactData['name'] ?? 'Unknown';
            echo "  [{$position}/{$total}] ✗ {$name} - {$e->getMessage()}\n";
        }
    }

    echo "\n=== Import Complete ===\n";
    echo "Success: " . count($results['success']) . "\n";
    echo "Failed: " . count($results['failed']) . "\n\n";

    if (!empty($results['failed'])) {
        echo "Failed contacts:\n";
        foreach ($results['failed'] as $item) {
            echo "  - {$item['data']['phone']}: {$item['error']}\n";
        }
    }

    return $results;
}

// Example 1: Import from CSV
echo "Example 1: Import from CSV file\n";
echo "================================\n\n";

// Create sample CSV if not exists
$csvFile = __DIR__ . '/sample-contacts.csv';
if (!file_exists($csvFile)) {
    $csv = "Name,Phone,Email,Company,Position\n";
    $csv .= "John Doe,6281234567890,john@example.com,PT Example,Manager\n";
    $csv .= "Jane Smith,6287654321098,jane@example.com,PT Demo,Director\n";
    $csv .= "Bob Johnson,6281111111111,bob@example.com,PT Test,CEO\n";
    file_put_contents($csvFile, $csv);
    echo "Sample CSV created: {$csvFile}\n\n";
}

try {
    $results = importFromCSV($chatbridge, $csvFile);
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n";
echo "Example 2: Import from Array\n";
echo "============================\n\n";

// Sample data
$contacts = [
    [
        'name' => 'Alice Williams',
        'phone' => '6282222222222',
        'email' => 'alice@example.com',
        'custom_fields' => [
            'company' => 'PT Alpha',
            'position' => 'VP Sales',
            'city' => 'Jakarta'
        ]
    ],
    [
        'name' => 'Charlie Brown',
        'phone' => '6283333333333',
        'email' => 'charlie@example.com',
        'custom_fields' => [
            'company' => 'PT Beta',
            'position' => 'Marketing Manager',
            'city' => 'Surabaya'
        ]
    ],
    [
        'name' => 'Diana Prince',
        'phone' => '6284444444444',
        'email' => 'diana@example.com',
        'custom_fields' => [
            'company' => 'PT Gamma',
            'position' => 'CTO',
            'city' => 'Bandung'
        ]
    ]
];

try {
    $results = importFromArray($chatbridge, $contacts);
    
    // Create audience from imported contacts
    if (!empty($results['success'])) {
        echo "\nCreating audience from imported contacts...\n";
        
        $audience = $chatbridge->audiences()->create([
            'name' => 'Bulk Import ' . date('Y-m-d H:i:s'),
            'description' => 'Contacts imported via bulk import',
            'tag' => 'bulk-import',
            'contacts' => array_map(function($contact) {
                return [
                    'phone' => $contact['phone'],
                    'name' => $contact['name']
                ];
            }, $results['success'])
        ]);
        
        echo "✓ Audience created: {$audience['name']}\n";
        echo "  ID: {$audience['id']}\n";
        echo "  Total contacts: {$audience['contacts_count']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
