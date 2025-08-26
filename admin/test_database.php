<?php
// Simple database connection test
header('Content-Type: text/html');

echo "<h1>Database Connection Test</h1>";

try {
    // Test 1: Check if database config file exists
    echo "<h2>Test 1: Database Configuration File</h2>";
    $projectRoot = dirname(__DIR__);
    $configFile = $projectRoot . '/config/database.php';
    
    if (file_exists($configFile)) {
        echo "✅ Database config file exists: $configFile<br>";
    } else {
        echo "❌ Database config file not found: $configFile<br>";
        echo "Current directory: " . getcwd() . "<br>";
        echo "Project root: $projectRoot<br>";
        exit;
    }
    
    // Test 2: Try to include database config
    echo "<h2>Test 2: Include Database Config</h2>";
    try {
        require_once $configFile;
        echo "✅ Database config included successfully<br>";
    } catch (Exception $e) {
        echo "❌ Failed to include database config: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // Test 3: Check if getDB function exists
    echo "<h2>Test 3: getDB Function</h2>";
    if (function_exists('getDB')) {
        echo "✅ getDB function exists<br>";
    } else {
        echo "❌ getDB function not found<br>";
        exit;
    }
    
    // Test 4: Try to get database connection
    echo "<h2>Test 4: Database Connection</h2>";
    try {
        $pdo = getDB();
        if ($pdo && $pdo instanceof PDO) {
            echo "✅ Database connection successful<br>";
            echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "<br>";
            echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
        } else {
            echo "❌ Database connection failed: getDB() returned null or invalid object<br>";
            exit;
        }
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // Test 5: Test basic database operations
    echo "<h2>Test 5: Basic Database Operations</h2>";
    try {
        // Test if we can execute a simple query
        $stmt = $pdo->query('SELECT 1 as test');
        $result = $stmt->fetch();
        if ($result && $result['test'] == 1) {
            echo "✅ Basic query execution successful<br>";
        } else {
            echo "❌ Basic query execution failed<br>";
        }
    } catch (Exception $e) {
        echo "❌ Basic query execution failed: " . $e->getMessage() . "<br>";
    }
    
    // Test 6: Check if media table exists
    echo "<h2>Test 6: Media Table Check</h2>";
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'media'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Media table exists<br>";
        } else {
            echo "ℹ️ Media table does not exist (will be created automatically)<br>";
        }
    } catch (Exception $e) {
        echo "❌ Media table check failed: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
    echo "<p><strong>Note:</strong> This test file helps identify database connection issues. Delete it after testing.</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Test Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 