<?php
// Prevent any output before JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../includes/Comment.php';
    require_once '../includes/User.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['post_id']) && isset($input['content']) && !empty($input['content'])) {
            $comment = new Comment();
            $user = new User();
            
            $postId = (int)$input['post_id'];
            $content = sanitize($input['content']);
            $userId = null;
            $guestName = null;
            $guestEmail = null;
            
            // Check if user is logged in
            if ($user->isLoggedIn()) {
                $currentUser = $user->getCurrentUser();
                $userId = $currentUser['id'];
            } else {
                // Guest comment
                $guestName = isset($input['guest_name']) ? sanitize($input['guest_name']) : 'Anonymous';
                $guestEmail = isset($input['guest_email']) ? sanitize($input['guest_email']) : null;
            }
            
            $result = $comment->addComment($postId, $content, $userId, $guestName, $guestEmail);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Comment submitted successfully and awaiting approval']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit comment']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 