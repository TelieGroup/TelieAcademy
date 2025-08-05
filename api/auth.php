<?php
// Prevent any output before JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Include session configuration
require_once '../config/session.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../includes/User.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action'])) {
            $user = new User();
            
            switch ($input['action']) {
                case 'login':
                    if (isset($input['username']) && isset($input['password'])) {
                        $result = $user->login($input['username'], $input['password']);
                        echo json_encode($result);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Missing username or password']);
                    }
                    break;
                    
                case 'logout':
                    $result = $user->logout();
                    echo json_encode($result);
                    break;
                    
                case 'register':
                    if (isset($input['username']) && isset($input['email']) && isset($input['password'])) {
                        $result = $user->register($input['username'], $input['email'], $input['password']);
                        echo json_encode($result);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                    }
                    break;
                    
                case 'check_status':
                    $isLoggedIn = $user->isLoggedIn();
                    $isPremium = $user->isPremium();
                    $currentUser = $user->getCurrentUser();
                    
                    echo json_encode([
                        'success' => true,
                        'is_logged_in' => $isLoggedIn,
                        'is_premium' => $isPremium,
                        'user' => $currentUser
                    ]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing action parameter']);
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