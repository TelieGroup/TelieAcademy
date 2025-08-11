<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/Bookmark.php';
require_once '../includes/User.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
$user = new User();
if (!$user->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

$currentUser = $user->getCurrentUser();
$bookmark = new Bookmark();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'user') {
                // Get user's bookmarks
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                
                $bookmarks = $bookmark->getUserBookmarks($currentUser['id'], $limit, $offset);
                $totalCount = $bookmark->getUserBookmarkCount($currentUser['id']);
                
                echo json_encode([
                    'success' => true,
                    'bookmarks' => $bookmarks,
                    'total' => $totalCount,
                    'hasMore' => ($offset + $limit) < $totalCount
                ]);
            } elseif ($action === 'check') {
                // Check if a specific post is bookmarked
                $postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
                
                if ($postId <= 0) {
                    throw new Exception('Invalid post ID');
                }
                
                $isBookmarked = $bookmark->isBookmarked($currentUser['id'], $postId);
                $bookmarkCount = $bookmark->getPostBookmarkCount($postId);
                
                echo json_encode([
                    'success' => true,
                    'isBookmarked' => $isBookmarked,
                    'bookmarkCount' => $bookmarkCount
                ]);
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        case 'POST':
            // Add bookmark
            $data = json_decode(file_get_contents('php://input'), true);
            $postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
            
            if ($postId <= 0) {
                throw new Exception('Invalid post ID');
            }
            
            $success = $bookmark->addBookmark($currentUser['id'], $postId);
            
            if ($success) {
                $bookmarkCount = $bookmark->getPostBookmarkCount($postId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Post bookmarked successfully',
                    'isBookmarked' => true,
                    'bookmarkCount' => $bookmarkCount
                ]);
            } else {
                throw new Exception('Failed to add bookmark');
            }
            break;
            
        case 'DELETE':
            // Remove bookmark
            $data = json_decode(file_get_contents('php://input'), true);
            $postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
            
            if ($postId <= 0) {
                throw new Exception('Invalid post ID');
            }
            
            $success = $bookmark->removeBookmark($currentUser['id'], $postId);
            
            if ($success) {
                $bookmarkCount = $bookmark->getPostBookmarkCount($postId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Bookmark removed successfully',
                    'isBookmarked' => false,
                    'bookmarkCount' => $bookmarkCount
                ]);
            } else {
                throw new Exception('Failed to remove bookmark');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
