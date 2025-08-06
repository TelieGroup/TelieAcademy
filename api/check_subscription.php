<?php
// Prevent any output before JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../includes/Newsletter.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['email']) && filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $newsletter = new Newsletter();
            $isSubscribed = $newsletter->isEmailSubscribed($input['email']);
            $subscriber = $newsletter->getSubscriberByEmail($input['email']);
            
            $result = [
                'success' => true,
                'is_subscribed' => $isSubscribed,
                'subscriber' => $subscriber
            ];
            
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