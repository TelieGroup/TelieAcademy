<?php
// Test script for newsletter functionality
// This file should be removed in production

require_once 'includes/EmailHelper.php';

echo "<h1>Newsletter Email Test</h1>";

try {
    $emailHelper = new EmailHelper();
    
    // Test email configuration
    $testEmail = 'test@example.com'; // Change this to your email for testing
    $subject = 'Test Newsletter - TelieAcademy';
    $content = '
        <h2>Welcome to TelieAcademy!</h2>
        <p>This is a test newsletter to verify our email system is working correctly.</p>
        <ul>
            <li>HTML formatting works</li>
            <li>Links are functional</li>
            <li>Images display properly</li>
        </ul>
        <p><strong>Thank you for testing our newsletter system!</strong></p>
    ';
    
    echo "<h3>Testing Email Functionality</h3>";
    echo "<p>Attempting to send test email to: <strong>$testEmail</strong></p>";
    
    // Send test email
    $result = $emailHelper->testEmail($testEmail, $subject, $content);
    
    if ($result['success']) {
        echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<strong>✓ Success:</strong> " . $result['message'];
        echo "</div>";
        
        echo "<h4>Email Details:</h4>";
        echo "<ul>";
        echo "<li><strong>To:</strong> $testEmail</li>";
        echo "<li><strong>Subject:</strong> $subject</li>";
        echo "<li><strong>Content:</strong> HTML formatted newsletter</li>";
        echo "<li><strong>Template:</strong> Default</li>";
        echo "</ul>";
        
        echo "<p><em>Check your email inbox for the test message.</em></p>";
        
    } else {
        echo "<div style='color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<strong>✗ Error:</strong> " . $result['message'];
        echo "</div>";
        
        echo "<h4>Troubleshooting Tips:</h4>";
        echo "<ul>";
        echo "<li>Check if PHP mail() function is enabled on your server</li>";
        echo "<li>Verify SMTP configuration in php.ini</li>";
        echo "<li>Check server error logs for mail-related errors</li>";
        echo "<li>Ensure the test email address is valid</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<strong>✗ Exception:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h3>System Information</h3>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>Mail Function:</strong> " . (function_exists('mail') ? 'Available' : 'Not Available') . "</li>";
echo "<li><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "</li>";
echo "<li><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</li>";
echo "</ul>";

echo "<p><strong>Note:</strong> This test file should be removed in production for security reasons.</p>";
?>
