<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/ContactMessage.php';

// Check if user is admin
$user = new User();
if (!$user->isLoggedIn() || !$user->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if message ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message ID is required']);
    exit();
}

$messageId = (int)$_GET['id'];
$contactMessage = new ContactMessage();

// Get message details
$message = $contactMessage->getMessageById($messageId);

if (!$message) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Message not found']);
    exit();
}

// Return message data
echo json_encode([
    'success' => true,
    'message' => $message
]);
?>

