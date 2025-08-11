<?php
// Test logout functionality
require_once 'config/session.php';
require_once 'includes/User.php';

$user = new User();

echo "<h2>Logout Test</h2>";

// Check if user is logged in
if ($user->isLoggedIn()) {
    $currentUser = $user->getCurrentUser();
    echo "<p>✅ User is logged in: " . htmlspecialchars($currentUser['username']) . "</p>";
    
    // Test logout
    echo "<p>Testing logout...</p>";
    $result = $user->logout();
    
    if ($result['success']) {
        echo "<p>✅ Logout successful: " . htmlspecialchars($result['message']) . "</p>";
        
        // Check if user is still logged in
        if (!$user->isLoggedIn()) {
            echo "<p>✅ User is no longer logged in</p>";
        } else {
            echo "<p>❌ User is still logged in after logout</p>";
        }
    } else {
        echo "<p>❌ Logout failed: " . htmlspecialchars($result['message']) . "</p>";
    }
} else {
    echo "<p>ℹ️ No user is currently logged in</p>";
}

echo "<p><a href='index.php'>Back to Homepage</a></p>";
?> 