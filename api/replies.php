<?php
require_once '../config/session.php';
require_once '../includes/Reply.php';
require_once '../includes/User.php';

header('Content-Type: application/json');

$reply = new Reply();
$user = new User();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Add a new reply
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['comment_id']) || !isset($input['content']) || empty(trim($input['content']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Comment ID and content are required']);
            exit;
        }

        $commentId = (int)$input['comment_id'];
        $content = trim($input['content']);
        $userId = null;
        $guestName = null;
        $guestEmail = null;

        // Check if user is logged in
        if ($user->isLoggedIn()) {
            $currentUser = $user->getCurrentUser();
            $userId = $currentUser['id'];
        } else {
            // Guest user
            if (!isset($input['guest_name']) || empty(trim($input['guest_name']))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Guest name is required for anonymous replies']);
                exit;
            }
            $guestName = trim($input['guest_name']);
            $guestEmail = isset($input['guest_email']) ? trim($input['guest_email']) : null;
        }

        // Validate content length
        if (strlen($content) > 1000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Reply content cannot exceed 1000 characters']);
            exit;
        }

        $result = $reply->addReply($commentId, $content, $userId, $guestName, $guestEmail);
        
        if ($result['success']) {
            http_response_code(201);
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
        break;

    case 'GET':
        // Get replies for a comment
        if (isset($_GET['comment_id'])) {
            $commentId = (int)$_GET['comment_id'];
            $replies = $reply->getRepliesByComment($commentId);
            echo json_encode(['success' => true, 'replies' => $replies]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
        }
        break;

    case 'DELETE':
        // Delete a reply (admin only)
        if (!$user->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }

        $currentUser = $user->getCurrentUser();
        if (!$currentUser['is_admin']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['reply_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Reply ID is required']);
            exit;
        }

        $replyId = (int)$input['reply_id'];
        $result = $reply->deleteReply($replyId);
        
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>


