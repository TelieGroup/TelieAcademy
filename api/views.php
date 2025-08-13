<?php
require_once '../config/session.php';
require_once '../includes/View.php';

header('Content-Type: application/json');

$view = new View();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Record a view for a post
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['post_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Post ID is required']);
            exit;
        }

        $postId = (int)$input['post_id'];
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // Get client IP address (handles proxy scenarios)
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_REAL_IP'];
        }

        $result = $view->recordView($postId, $ipAddress, $userAgent);
        
        if ($result['success']) {
            // Get updated view count
            $viewCount = $view->getViewCount($postId);
            echo json_encode([
                'success' => true, 
                'message' => $result['message'],
                'view_count' => $viewCount
            ]);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
        break;

    case 'GET':
        // Get view statistics for a post
        if (isset($_GET['post_id'])) {
            $postId = (int)$_GET['post_id'];
            $stats = $view->getPostViewStats($postId);
            echo json_encode(['success' => true, 'stats' => $stats]);
        } else {
            // Get overall view statistics
            $stats = $view->getOverallViewStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>

