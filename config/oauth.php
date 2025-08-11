<?php
// OAuth Configuration
// This file contains OAuth provider settings

// LinkedIn OAuth Configuration
// IMPORTANT: Make sure this redirect URI matches exactly what's registered in your LinkedIn app
// Go to https://www.linkedin.com/developers/ → Your App → Auth → Redirect URLs
// Add: http://localhost/TelieAcademy/auth/linkedin-callback.php
define('LINKEDIN_CLIENT_ID', '77iddli7h5um84');
define('LINKEDIN_CLIENT_SECRET', 'WPL_AP1.hurS78oaun1H4fGC.2pVudQ==');
define('LINKEDIN_REDIRECT_URI', 'http://localhost/TelieAcademy/auth/linkedin-callback.php');

// Google OAuth Configuration (for future use)
define('GOOGLE_CLIENT_ID', '226293164471-jovp54pu718kl674dk8pmmvbugih7npf.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/TelieAcademy/auth/google-callback.php');

// GitHub OAuth Configuration (for future use)
define('GITHUB_CLIENT_ID', 'Iv23liHS3DevnnOZnVxX');
define('GITHUB_CLIENT_SECRET', 'e86d476be76ef79de2870dbbf022a4be2e1f57bd');
define('GITHUB_REDIRECT_URI', 'http://localhost/TelieAcademy/auth/github-callback.php');

// Email Configuration for verification emails
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_FROM_EMAIL', 'noreply@telieacademy.com');
define('SMTP_FROM_NAME', 'TelieAcademy');

// Site Configuration
define('SITE_URL', 'http://localhost/TelieAcademy');
define('SITE_NAME', 'TelieAcademy');

// Security Configuration
define('JWT_SECRET', 'your_jwt_secret_key_here');
define('EMAIL_VERIFICATION_EXPIRY', 24 * 60 * 60); // 24 hours in seconds
define('PASSWORD_RESET_EXPIRY', 1 * 60 * 60); // 1 hour in seconds

// OAuth State Management
function generateOAuthState() {
    return bin2hex(random_bytes(32));
}

function verifyOAuthState($state) {
    return isset($_SESSION['oauth_state']) && hash_equals($_SESSION['oauth_state'], $state);
}

function storeOAuthState($state) {
    $_SESSION['oauth_state'] = $state;
}

// Check if OAuth credentials are configured
function isLinkedInConfigured() {
    return defined('LINKEDIN_CLIENT_ID') && LINKEDIN_CLIENT_ID !== 'your_linkedin_client_id' && !empty(LINKEDIN_CLIENT_ID);
}

function isGoogleConfigured() {
    return defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== 'your_google_client_id' && !empty(GOOGLE_CLIENT_ID);
}

function isGitHubConfigured() {
    return defined('GITHUB_CLIENT_ID') && GITHUB_CLIENT_ID !== 'your_github_client_id' && !empty(GITHUB_CLIENT_ID);
}

// LinkedIn OAuth URLs
function getLinkedInAuthUrl($state) {
    if (!isLinkedInConfigured()) {
        throw new Exception('LinkedIn OAuth is not configured. Please set up your LinkedIn app credentials in config/oauth.php');
    }
    
    $params = [
        'response_type' => 'code',
        'client_id' => LINKEDIN_CLIENT_ID,
        'redirect_uri' => LINKEDIN_REDIRECT_URI,
        'state' => $state,
        'scope' => 'r_liteprofile r_emailaddress'
    ];
    
    return 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);
}

// Google OAuth URLs (for future use)
function getGoogleAuthUrl($state) {
    if (!isGoogleConfigured()) {
        throw new Exception('Google OAuth is not configured. Please set up your Google app credentials in config/oauth.php');
    }
    
    $params = [
        'response_type' => 'code',
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'state' => $state,
        'scope' => 'openid email profile'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

// GitHub OAuth URLs (for future use)
function getGitHubAuthUrl($state) {
    if (!isGitHubConfigured()) {
        throw new Exception('GitHub OAuth is not configured. Please set up your GitHub app credentials in config/oauth.php');
    }
    
    $params = [
        'response_type' => 'code',
        'client_id' => GITHUB_CLIENT_ID,
        'redirect_uri' => GITHUB_REDIRECT_URI,
        'state' => $state,
        'scope' => 'read:user user:email'
    ];
    
    return 'https://github.com/login/oauth/authorize?' . http_build_query($params);
}
?> 