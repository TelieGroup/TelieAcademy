<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output, just log them

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header immediately
header('Content-Type: application/json');

try {
    require_once '../config/session.php';
    require_once '../includes/User.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load required files: ' . $e->getMessage()]);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    $user = new User();
    $userId = $_SESSION['user_id'];
    
    // Get current user info to check if they're an OAuth user
    $currentUser = $user->getCurrentUser();
    if (!$currentUser) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to initialize user: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['profile_picture']) ? $_FILES['profile_picture']['error'] : 'No file uploaded';
            throw new Exception('No file uploaded or upload error occurred. Error code: ' . $error);
        }

        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed. Received: ' . $file['type']);
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large. Maximum size is 5MB. Received: ' . $file['size'] . ' bytes');
        }

        // Create uploads directory if it doesn't exist
        $uploadDir = '../uploads/profile-pictures/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory: ' . $uploadDir);
            }
        }

        // Check if directory is writable
        if (!is_writable($uploadDir)) {
            throw new Exception('Upload directory is not writable: ' . $uploadDir);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to save uploaded file to: ' . $filepath);
        }

        // Check if user currently has an OAuth profile picture
        $isOAuthUser = !empty($currentUser['oauth_provider']) && $currentUser['oauth_provider'] !== 'email';
        $hasOAuthPicture = false;
        
        if ($currentUser['profile_picture']) {
            // Check if current profile picture is from OAuth
            $hasOAuthPicture = filter_var($currentUser['profile_picture'], FILTER_VALIDATE_URL) || 
                              strpos($currentUser['profile_picture'], 'googleusercontent.com') !== false ||
                              strpos($currentUser['profile_picture'], 'githubusercontent.com') !== false ||
                              strpos($currentUser['profile_picture'], 'licdn.com') !== false;
        }

        // Update user's profile picture in database
        $result = $user->updateProfilePicture($userId, 'uploads/profile-pictures/' . $filename);
        
        if ($result['success']) {
            $message = 'Profile picture updated successfully';
            if ($hasOAuthPicture) {
                $message .= ' (replaced your OAuth profile picture)';
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'profile_picture' => 'uploads/profile-pictures/' . $filename,
                'is_oauth_user' => $isOAuthUser,
                'replaced_oauth_picture' => $hasOAuthPicture
            ]);
        } else {
            // Delete uploaded file if database update failed
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            throw new Exception($result['message']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 