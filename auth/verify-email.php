<?php
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/includes/User.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$messageType = '';

try {
    if (!isset($_GET['token'])) {
        throw new Exception('No verification token provided');
    }
    
    $token = $_GET['token'];
    
    // Verify email
    $user = new User();
    $result = $user->verifyEmail($token);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
    
} catch (Exception $e) {
    $message = 'Verification failed: ' . $e->getMessage();
    $messageType = 'error';
}

// Store message in session for display on homepage
$_SESSION['auth_message'] = $message;
$_SESSION['auth_message_type'] = $messageType;

// Redirect to homepage
header('Location: ../index.php');
exit;
?> 