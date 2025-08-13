<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing view recording...\n";

try {
    require_once 'includes/View.php';
    echo "View class loaded\n";
    
    $view = new View();
    echo "View object created\n";
    
    // Test recording a view for post ID 1
    $postId = 1;
    $ipAddress = '127.0.0.1';
    $userAgent = 'Test User Agent';
    
    echo "Recording view for post ID: {$postId}\n";
    echo "IP Address: {$ipAddress}\n";
    echo "User Agent: {$userAgent}\n\n";
    
    $result = $view->recordView($postId, $ipAddress, $userAgent);
    
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
    if ($result['success']) {
        echo "\n✓ View recorded successfully!\n";
        
        // Check the updated view count
        $viewCount = $view->getViewCount($postId);
        echo "Updated view count: {$viewCount}\n";
        
        // Check detailed stats
        $stats = $view->getPostViewStats($postId);
        echo "Detailed stats: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";
        
    } else {
        echo "\n✗ Failed to record view: {$result['message']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
