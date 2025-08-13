<?php
require_once 'includes/EmailHelper.php';

$emailHelper = new EmailHelper();

echo "=== Testing Unsubscribe Link Generation ===\n\n";

// Test with a sample email
$testEmail = 'test@example.com';

// Create a test subscriber array
$testSubscriber = [
    'email' => $testEmail,
    'name' => 'Test User'
];

// Test the generateUnsubscribeLink method using reflection
$reflection = new ReflectionClass($emailHelper);
$method = $reflection->getMethod('generateUnsubscribeLink');
$method->setAccessible(true);

try {
    $unsubscribeLink = $method->invoke($emailHelper, $testEmail);
    echo "Generated unsubscribe link:\n";
    echo $unsubscribeLink . "\n\n";
    
    // Parse the URL to see the components
    $parsed = parse_url($unsubscribeLink);
    echo "URL Components:\n";
    echo "Protocol: " . ($parsed['scheme'] ?? 'N/A') . "\n";
    echo "Host: " . ($parsed['host'] ?? 'N/A') . "\n";
    echo "Path: " . ($parsed['path'] ?? 'N/A') . "\n";
    echo "Query: " . ($parsed['query'] ?? 'N/A') . "\n\n";
    
    // Check if the link is accessible
    echo "Testing link accessibility...\n";
    $testUrl = $unsubscribeLink;
    
    // Remove the token to test the basic page
    $baseUrl = strtok($testUrl, '?');
    echo "Base URL: " . $baseUrl . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
