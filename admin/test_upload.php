<?php
// Set content type to JSON
header('Content-Type: application/json');

// Simple test without authentication
try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
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
        
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }
    
    $file = $_FILES['image'];
    $altText = $_POST['alt_text'] ?? '';
    $caption = $_POST['caption'] ?? '';
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.']);
        exit;
    }
    
    // Validate file size (5MB limit)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB.']);
        exit;
    }
    
    // Get the absolute path to the project root
    $projectRoot = dirname(__DIR__);
    
    // Create upload directory if it doesn't exist
    $uploadDir = $projectRoot . '/uploads/posts/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
            exit;
        }
    }
    
    // Generate unique filename
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $uniqueName = uniqid('test_img_') . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueName;
    
    // Move uploaded file to destination
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file.']);
        exit;
    }
    
    // Get file dimensions for images
    $imageInfo = getimagesize($uploadPath);
    $width = $imageInfo[0] ?? null;
    $height = $imageInfo[1] ?? null;
    
    echo json_encode([
        'success' => true,
        'message' => 'Test image uploaded successfully',
        'file_path' => 'uploads/posts/' . $uniqueName,
        'filename' => $uniqueName,
        'original_name' => $file['name'],
        'width' => $width,
        'height' => $height,
        'alt_text' => $altText,
        'caption' => $caption
    ]);
    
} catch (Exception $e) {
    // Catch any other errors and return them as JSON
    error_log("Test upload error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    // Catch fatal errors
    error_log("Fatal test upload error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Fatal server error: ' . $e->getMessage()
    ]);
}
?> 