<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Newsletter.php';

// Set JSON response headers
header('Content-Type: application/json');

try {
    // Check if user is logged in and is admin
    $user = new User();
    if (!$user->isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }

    $currentUser = $user->getCurrentUser();
    if (!$currentUser || !$currentUser['is_admin']) {
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }

    // Get feedback ID from request
    $feedbackId = $_GET['id'] ?? null;
    if (!$feedbackId) {
        echo json_encode(['success' => false, 'message' => 'Feedback ID required']);
        exit;
    }

    $newsletter = new Newsletter();
    
    // Get feedback details
    $feedback = $newsletter->getFeedbackById($feedbackId);
    
    if ($feedback) {
        echo json_encode([
            'success' => true,
            'feedback' => $feedback
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Feedback not found'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage()
    ]);
}
?>
