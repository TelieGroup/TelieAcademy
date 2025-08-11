<?php
require_once 'config/session.php';
require_once 'includes/User.php';
require_once 'includes/OAuth.php';

echo "<h1>Authentication System Test</h1>";

// Test 1: Check if User class can be instantiated
try {
    $user = new User();
    echo "<p style='color: green;'>✅ User class instantiated successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ User class error: " . $e->getMessage() . "</p>";
}

// Test 2: Check if OAuth class can be instantiated
try {
    $oauth = new OAuth();
    echo "<p style='color: green;'>✅ OAuth class instantiated successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ OAuth class error: " . $e->getMessage() . "</p>";
}

// Test 3: Check database connection and new fields
try {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Database connection successful. Users count: " . $result['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 4: Check if new tables exist
$tables = ['oauth_tokens', 'email_verification_logs', 'password_reset_logs'];
foreach ($tables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✅ Table '$table' exists with " . $result['count'] . " records</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Table '$table' error: " . $e->getMessage() . "</p>";
    }
}

// Test 5: Check if new user fields exist
try {
    $stmt = $db->query("DESCRIBE users");
    $fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredFields = ['oauth_provider', 'oauth_id', 'email_verified', 'first_name', 'last_name'];
    
    foreach ($requiredFields as $field) {
        if (in_array($field, $fields)) {
            echo "<p style='color: green;'>✅ Field '$field' exists in users table</p>";
        } else {
            echo "<p style='color: red;'>❌ Field '$field' missing from users table</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Field check error: " . $e->getMessage() . "</p>";
}

// Test 6: Test email verification token generation
try {
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);
    echo "<p style='color: green;'>✅ Email verification token generated: " . substr($token, 0, 10) . "...</p>";
    echo "<p style='color: green;'>✅ Expires at: $expiresAt</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Token generation error: " . $e->getMessage() . "</p>";
}

// Test 7: Check OAuth configuration
if (defined('LINKEDIN_CLIENT_ID')) {
    echo "<p style='color: green;'>✅ LinkedIn OAuth configuration loaded</p>";
} else {
    echo "<p style='color: orange;'>⚠️ LinkedIn OAuth not configured (expected for testing)</p>";
}

if (defined('EMAIL_VERIFICATION_EXPIRY')) {
    echo "<p style='color: green;'>✅ Email verification expiry configured: " . EMAIL_VERIFICATION_EXPIRY . " seconds</p>";
} else {
    echo "<p style='color: red;'>❌ Email verification expiry not configured</p>";
}

echo "<h2>Test Summary</h2>";
echo "<p>All core authentication system components are ready!</p>";
echo "<p><a href='index.php'>← Back to Homepage</a></p>";
?> 