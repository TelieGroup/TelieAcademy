<?php
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/oauth.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if GitHub is configured
if (!isGitHubConfigured()) {
    // Redirect back to homepage with error message
    $_SESSION['auth_error'] = 'GitHub OAuth is not configured. Please contact the administrator.';
    header('Location: ../index.php');
    exit;
}

// Get state parameter
$state = $_GET['state'] ?? '';

if (empty($state)) {
    // Generate new state if not provided
    $state = generateOAuthState();
}

// Store state in session
storeOAuthState($state);

try {
    // Redirect to GitHub OAuth
    $authUrl = getGitHubAuthUrl($state);
    header('Location: ' . $authUrl);
    exit;
} catch (Exception $e) {
    // Redirect back to homepage with error message
    $_SESSION['auth_error'] = $e->getMessage();
    header('Location: ../index.php');
    exit;
}
?>
