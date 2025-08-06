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
            echo json_encode(['success' => false, 'message' => 'Login required. Please login or register to subscribe.']);
            exit;
        }
        
        $currentUser = $user->getCurrentUser();
        
        if (isset($input['email']) && filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $newsletter = new Newsletter();
            
            // Extract enhanced data
            $email = $input['email'];
            $name = isset($input['name']) && !empty($input['name']) ? $input['name'] : $currentUser['username'];
            $preferences = isset($input['preferences']) ? $input['preferences'] : null;
            $frequency = isset($input['frequency']) ? $input['frequency'] : 'weekly';
            $source = isset($input['source']) ? $input['source'] : 'website';
            $subscriptionType = isset($input['subscription_type']) ? $input['subscription_type'] : 'newsletter';
            
            // Validate frequency
            if (!in_array($frequency, ['daily', 'weekly', 'monthly'])) {
                $frequency = 'weekly';
            }
            
            // Validate subscription type
            if (!in_array($subscriptionType, ['newsletter', 'premium'])) {
                $subscriptionType = 'newsletter';
            }
            
            // Ensure email matches logged-in user
            if ($email !== $currentUser['email']) {
                echo json_encode(['success' => false, 'message' => 'Email must match your logged-in account']);
                exit;
            }
            
            $result = $newsletter->subscribe($email, $name, $preferences, $frequency, $source, $currentUser['id'], $subscriptionType);
            
            // If premium subscription was successful, include updated user status
            if ($result['success'] && $subscriptionType === 'premium') {
                // Get updated user info
                $user = new User();
                $updatedUser = $user->getCurrentUser();
                
                $result['user_updated'] = true;
                $result['is_premium'] = $updatedUser['is_premium'] ?? false;
                $result['message'] .= ' You now have access to premium content!';
            }
            
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
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