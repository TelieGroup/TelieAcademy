<?php
echo "<h1>TelieAcademy Dynamic Blog Setup</h1>";

if (!file_exists('config/database.php')) {
    echo "<div style='color: red;'>❌ Database configuration not found.</div>";
    exit;
}

require_once 'config/database.php';

try {
    $db = getDB();
    echo "<div style='color: green;'>✅ Database connection successful!</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    echo "<h3>Setup Instructions:</h3>";
    echo "<ol>";
    echo "<li>Start XAMPP (Apache and MySQL)</li>";
    echo "<li>Create database 'telie_academy' in phpMyAdmin</li>";
    echo "<li>Import database.sql into your database</li>";
    echo "<li>Access blog at http://localhost/TelieAcademy/</li>";
    echo "</ol>";
}

echo "<h3>Test Accounts:</h3>";
echo "<ul>";
echo "<li>admin / password (Premium)</li>";
echo "<li>john_doe / password (Regular)</li>";
echo "<li>jane_smith / password (Premium)</li>";
echo "</ul>";
?> 