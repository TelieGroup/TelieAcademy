<?php
// Simple test to verify upload handler is working
header('Content-Type: text/html');

echo "<h1>Upload Handler Test</h1>";

// Test 1: Check if file exists
echo "<h2>Test 1: File Existence</h2>";
if (file_exists('upload_image.php')) {
    echo "✅ upload_image.php exists<br>";
} else {
    echo "❌ upload_image.php not found<br>";
}

// Test 2: Check file permissions
echo "<h2>Test 2: File Permissions</h2>";
if (is_readable('upload_image.php')) {
    echo "✅ upload_image.php is readable<br>";
} else {
    echo "❌ upload_image.php is not readable<br>";
}

// Test 3: Check syntax
echo "<h2>Test 3: PHP Syntax</h2>";
$output = shell_exec('php -l upload_image.php 2>&1');
if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ upload_image.php has no syntax errors<br>";
} else {
    echo "❌ upload_image.php has syntax errors:<br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
}

// Test 4: Check uploads directory
echo "<h2>Test 4: Uploads Directory</h2>";
$uploadDir = '../uploads/posts/';
if (is_dir($uploadDir)) {
    echo "✅ Uploads directory exists: $uploadDir<br>";
    if (is_writable($uploadDir)) {
        echo "✅ Uploads directory is writable<br>";
    } else {
        echo "❌ Uploads directory is not writable<br>";
    }
} else {
    echo "❌ Uploads directory not found: $uploadDir<br>";
}

// Test 5: Check session configuration
echo "<h2>Test 5: Session Configuration</h2>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

echo "<hr>";
echo "<p><strong>Note:</strong> This test file helps identify configuration issues. Delete it after testing.</p>";
?> 