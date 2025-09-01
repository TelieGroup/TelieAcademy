<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/Course.php';

// Check if module_id is provided
if (!isset($_GET['module_id']) || empty($_GET['module_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Module ID is required']);
    exit;
}

$moduleId = (int)$_GET['module_id'];

try {
    $course = new Course();
    
    // Get posts for this module
    $posts = $course->getPostsByModule($moduleId, false); // Get all posts, not just active ones for admin
    
    // Format the response
    $formattedPosts = [];
    foreach ($posts as $post) {
        $formattedPosts[] = [
            'id' => $post['id'],
            'title' => $post['title'],
            'lesson_order' => $post['lesson_order'] ?? 0,
            'status' => $post['status'] ?? 'published'
        ];
    }
    
    // Sort by lesson order
    usort($formattedPosts, function($a, $b) {
        return $a['lesson_order'] <=> $b['lesson_order'];
    });
    
    echo json_encode($formattedPosts);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve posts: ' . $e->getMessage()]);
}
?>

