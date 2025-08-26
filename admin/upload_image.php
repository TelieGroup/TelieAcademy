<?php
// Prevent any output before headers
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Log the request for debugging
error_log("Upload request received: " . json_encode([
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'files' => isset($_FILES) ? array_keys($_FILES) : [],
    'post_data' => array_keys($_POST ?? [])
]));

// Simple session check without external dependencies
session_start();

// Check if user is logged in (simple check)
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Log session information for debugging
error_log("Session check: logged_in=$isLoggedIn, admin=$isAdmin, user_id=" . ($_SESSION['user_id'] ?? 'none'));

if (!$isLoggedIn) {
    echo json_encode([
        'success' => false, 
        'message' => 'User not logged in',
        'session_data' => $_SESSION ?? 'no session data'
    ]);
    exit;
}

if (!$isAdmin) {
    echo json_encode([
        'success' => false, 
        'message' => 'Admin access required',
        'session_data' => $_SESSION ?? 'no session data'
    ]);
    exit;
}

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
    
    // Log file information for debugging
    error_log("File upload: " . json_encode([
        'name' => $file['name'],
        'type' => $file['type'],
        'size' => $file['size'],
        'error' => $file['error']
    ]));
    
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
    $uniqueName = uniqid('post_img_') . '_' . time() . '.' . $fileExtension;
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
    
    // Log successful upload for debugging
    error_log("File uploaded successfully: $uploadPath");
    
    // Try to save to database (optional - will continue even if this fails)
    $mediaId = null;
    $databaseSuccess = false;
    
    try {
        // Check if database configuration file exists
        if (file_exists($projectRoot . '/config/database.php')) {
            require_once $projectRoot . '/config/database.php';
            
            // Try to get database connection
            $pdo = getDB();
            
            if ($pdo && $pdo instanceof PDO) {
                // Create media table if it doesn't exist
                $createTableSQL = "
                    CREATE TABLE IF NOT EXISTS media (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        filename VARCHAR(255) NOT NULL,
                        original_name VARCHAR(255) NOT NULL,
                        file_path VARCHAR(500) NOT NULL,
                        file_type VARCHAR(100) NOT NULL,
                        file_size INT NOT NULL,
                        width INT,
                        height INT,
                        alt_text TEXT,
                        caption TEXT,
                        uploaded_by INT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
                    )
                ";
                
                $pdo->exec($createTableSQL);
                
                // Insert media record
                $insertSQL = "
                    INSERT INTO media (filename, original_name, file_path, file_type, file_size, width, height, alt_text, caption, uploaded_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                
                $stmt = $pdo->prepare($insertSQL);
                $stmt->execute([
                    $uniqueName,
                    $file['name'],
                    'uploads/posts/' . $uniqueName,
                    $file['type'],
                    $file['size'],
                    $width,
                    $height,
                    $altText,
                    $caption,
                    $_SESSION['user_id']
                ]);
                
                $mediaId = $pdo->lastInsertId();
                $databaseSuccess = true;
                
                error_log("Media record saved to database with ID: $mediaId");
            } else {
                error_log("Database connection failed: getDB() returned null or invalid object");
            }
        } else {
            error_log("Database configuration file not found");
        }
    } catch (Exception $e) {
        // Log the database error but don't fail the upload
        error_log("Database operation failed: " . $e->getMessage());
        $databaseSuccess = false;
    } catch (Error $e) {
        // Log the database error but don't fail the upload
        error_log("Database operation failed: " . $e->getMessage());
        $databaseSuccess = false;
    }
    
    // Always return success for the file upload, regardless of database status
    $response = [
        'success' => true,
        'message' => $databaseSuccess ? 'Image uploaded successfully' : 'Image uploaded successfully (database record not saved)',
        'file_path' => 'uploads/posts/' . $uniqueName,
        'filename' => $uniqueName,
        'original_name' => $file['name'],
        'width' => $width,
        'height' => $height
    ];
    
    // Add database information if available
    if ($databaseSuccess && $mediaId) {
        $response['media_id'] = $mediaId;
        $response['database_saved'] = true;
    } else {
        $response['database_saved'] = false;
        $response['database_note'] = 'Database operations failed, but file upload succeeded';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Catch any other errors and return them as JSON
    error_log("Upload error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    // Catch fatal errors
    error_log("Fatal upload error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Fatal server error: ' . $e->getMessage()
    ]);
}

// Flush output buffer to ensure JSON is sent
ob_end_flush();
?> 