<?php
// Prevent any output before JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../config/session.php';
    require_once '../includes/Newsletter.php';
    require_once '../includes/User.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if user is logged in
        $user = new User();
        if (!$user->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Login required']);
            exit;
        }
        
        $currentUser = $user->getCurrentUser();
        $newsletter = new Newsletter();
        
        // Get subscription info
        $subscription = $newsletter->getUserSubscription($currentUser['id'], $currentUser['email']);
        
        if (!$subscription || !$subscription['is_active']) {
            echo json_encode(['success' => false, 'message' => 'No active subscription found']);
            exit;
        }
        
        // Unsubscribe using email
        $result = $newsletter->unsubscribe($currentUser['email']);
        
        if ($result['success']) {
            // If it was a premium subscription, include status update info
            if ($subscription['subscription_type'] === 'premium') {
                $result['premium_removed'] = true;
                $result['message'] = 'Successfully unsubscribed from premium newsletter. Your premium access has been revoked.';
            } else {
                $result['message'] = 'Successfully unsubscribed from newsletter.';
            }
        }
        
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>