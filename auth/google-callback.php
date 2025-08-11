<?php
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/includes/OAuth.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if we have the required parameters
    if (!isset($_GET['code']) || !isset($_GET['state'])) {
        throw new Exception('Missing required OAuth parameters');
    }
    
    $code = $_GET['code'];
    $state = $_GET['state'];
    
    // Handle Google authentication
    $oauth = new OAuth();
    $result = $oauth->handleGoogleCallback($code, $state);
    
    if ($result['success']) {
        // Success - redirect to homepage with success message
        $_SESSION['auth_message'] = 'Google login successful! Welcome back.';
        $_SESSION['auth_message_type'] = 'success';
        header('Location: ../index.php');
        exit;
    } else {
        // Error - redirect to login with error message
        $_SESSION['auth_message'] = 'Google login failed: ' . $result['message'];
        $_SESSION['auth_message_type'] = 'error';
        header('Location: ../index.php');
        exit;
    }
    
} catch (Exception $e) {
    // Exception - redirect to login with error message
    $_SESSION['auth_message'] = 'Authentication error: ' . $e->getMessage();
    $_SESSION['auth_message_type'] = 'error';
    header('Location: ../index.php');
    exit;
}
?> 