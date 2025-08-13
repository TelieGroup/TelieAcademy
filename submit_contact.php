<?php
require_once 'config/session.php';
require_once 'includes/User.php';
require_once 'includes/ContactMessage.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Validate required fields
$requiredFields = ['first_name', 'last_name', 'email', 'subject', 'message'];
foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

// Validate email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

// Check if user is logged in
$user = new User();
$isLoggedIn = $user->isLoggedIn();
$currentUser = null;

if ($isLoggedIn) {
    $currentUser = $user->getCurrentUser();
    // Verify that the logged-in user's email matches the submitted email
    if ($currentUser['email'] !== $input['email']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email address must match your registered account']);
        exit();
    }
}

// Prepare data for saving
$messageData = [
    'user_id' => $isLoggedIn ? $currentUser['id'] : null,
    'first_name' => trim($input['first_name']),
    'last_name' => trim($input['last_name']),
    'email' => trim($input['email']),
    'phone' => !empty($input['phone']) ? trim($input['phone']) : null,
    'subject' => trim($input['subject']),
    'message' => trim($input['message']),
    'newsletter_subscribe' => !empty($input['newsletter_subscribe']) ? 1 : 0
];

// Create contact message
$contactMessage = new ContactMessage();
$messageId = $contactMessage->createMessage($messageData);

if ($messageId) {
    // Success
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'message_id' => $messageId
    ]);
} else {
    // Error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save message. Please try again.'
    ]);
}
?>

