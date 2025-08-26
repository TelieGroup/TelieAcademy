<?php
// Set content type to JSON
header('Content-Type: application/json');

// Debug information
$debug = [
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_save_path' => session_save_path(),
    'cookies' => $_COOKIE,
    'post_data' => $_POST,
    'files' => isset($_FILES) ? array_keys($_FILES) : [],
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'http_referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown',
    'http_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
];

try {
    // Get the absolute path to the project root
    $projectRoot = dirname(__DIR__);
    
    // Try to include session and user files
    $sessionIncluded = false;
    $userIncluded = false;
    
    try {
        require_once $projectRoot . '/config/session.php';
        $sessionIncluded = true;
        $debug['session_after_include'] = [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_data' => $_SESSION ?? 'no session data'
        ];
    } catch (Exception $e) {
        $debug['session_error'] = $e->getMessage();
    }
    
    try {
        require_once $projectRoot . '/includes/User.php';
        $userIncluded = true;
    } catch (Exception $e) {
        $debug['user_error'] = $e->getMessage();
    }
    
    // Check authentication if files were included successfully
    if ($sessionIncluded && $userIncluded) {
        $user = new User();
        $isLoggedIn = $user->isLoggedIn();
        $currentUser = $user->getCurrentUser();
        
        $debug['auth_info'] = [
            'is_logged_in' => $isLoggedIn,
            'current_user' => $currentUser ? [
                'id' => $currentUser['id'],
                'username' => $currentUser['username'],
                'is_admin' => $currentUser['is_admin'] ?? false
            ] : null
        ];
        
        if (!$isLoggedIn) {
            echo json_encode([
                'success' => false, 
                'message' => 'User not logged in',
                'debug' => $debug
            ]);
            exit;
        }
        
        if (!$currentUser || !($currentUser['is_admin'] ?? false)) {
            echo json_encode([
                'success' => false, 
                'message' => 'User not admin',
                'debug' => $debug
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Required files could not be included',
            'debug' => $debug
        ]);
        exit;
    }
    
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false, 
            'message' => 'Method not allowed',
            'debug' => $debug
        ]);
        exit;
    }
    
    // Check if image file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'No image uploaded';
        if (isset($_FILES['image']['error'])) {
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMessage = 'File too large';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMessage = 'File upload was incomplete';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMessage = 'No file was uploaded';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMessage = 'Missing temporary folder';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMessage = 'Failed to write file to disk';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMessage = 'File upload stopped by extension';
                    break;
            }
        }
        
        echo json_encode([
            'success' => false, 
            'message' => $errorMessage,
            'debug' => $debug
        ]);
        exit;
    }
    
    $file = $_FILES['image'];
    $altText = $_POST['alt_text'] ?? '';
    $caption = $_POST['caption'] ?? '';
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.',
            'debug' => $debug
        ]);
        exit;
    }
    
    // Validate file size (5MB limit)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        echo json_encode([
            'success' => false, 
            'message' => 'File size must be less than 5MB.',
            'debug' => $debug
        ]);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = $projectRoot . '/uploads/posts/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to create upload directory',
                'debug' => $debug
            ]);
            exit;
        }
    }
    
    // Generate unique filename
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $uniqueName = uniqid('debug_img_') . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueName;
    
    // Move uploaded file to destination
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to save uploaded file.',
            'debug' => $debug
        ]);
        exit;
    }
    
    // Get file dimensions for images
    $imageInfo = getimagesize($uploadPath);
    $width = $imageInfo[0] ?? null;
    $height = $imageInfo[1] ?? null;
    
    echo json_encode([
        'success' => true,
        'message' => 'Debug image uploaded successfully',
        'file_path' => 'uploads/posts/' . $uniqueName,
        'filename' => $uniqueName,
        'original_name' => $file['name'],
        'width' => $width,
        'height' => $height,
        'debug' => $debug
    ]);
    
} catch (Exception $e) {
    // Catch any other errors and return them as JSON
    error_log("Debug upload error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage(),
        'debug' => $debug
    ]);
} catch (Error $e) {
    // Catch fatal errors
    error_log("Fatal debug upload error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Fatal server error: ' . $e->getMessage(),
        'debug' => $debug
    ]);
}
?> 