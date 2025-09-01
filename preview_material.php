<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: login');
    exit;
}

$currentUser = $user->getCurrentUser();

// Check if user has access (either admin or premium)
$hasAccess = false;
$accessType = '';

if ($currentUser['is_admin']) {
    $hasAccess = true;
    $accessType = 'admin';
} elseif ($currentUser['is_premium']) {
    $hasAccess = true;
    $accessType = 'premium';
}

if (!$hasAccess) {
    // Log access attempt
    error_log("Unauthorized preview attempt by user ID: {$currentUser['id']} (Premium: {$currentUser['is_premium']}, Admin: {$currentUser['is_admin']})");
    
    header('Location: index?premium_required=1');
    exit;
}

// Get material ID from URL
$materialId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$materialId) {
    header('Location: courses');
    exit;
}

// Get material details
$material = $course->getMaterialById($materialId);
if (!$material) {
    error_log("Material not found for ID: $materialId");
    header('Location: courses');
    exit;
}

// Check if material is part of a course and user is enrolled
if (!empty($material['module_id'])) {
    $module = $course->getModuleById($material['module_id']);
    if ($module) {
        $courseData = $course->getCourseById($module['course_id']);
        if ($courseData) {
            $enrollment = $course->getUserCourseEnrollment($currentUser['id'], $courseData['id']);
            if (!$enrollment) {
                error_log("Unauthorized material access - User not enrolled in course. User ID: {$currentUser['id']}, Course ID: {$courseData['id']}, Material ID: $materialId");
                header('Location: course-view?course=' . $courseData['slug'] . '&error=enrollment_required');
                exit;
            }
        }
    }
}

// Fix file path - remove ../ if present and ensure correct path
$filePath = $material['file_path'];
if (strpos($filePath, '../') === 0) {
    $filePath = substr($filePath, 3); // Remove ../ from the beginning
}

// Check if file exists
if (!file_exists($filePath)) {
    error_log("File not found on disk: {$filePath} for material ID: $materialId");
    error_log("Original path: {$material['file_path']}, Fixed path: $filePath");
    header('Location: courses');
    exit;
}

// Record preview access and increment preview count
try {
    $course->recordUserAccess($currentUser['id'], $materialId);
    $course->incrementPreviewCount($materialId);
    
    // Log successful preview
    error_log("Material preview accessed - User: {$currentUser['id']} ({$currentUser['username']}), Type: $accessType, Material: {$material['title']} (ID: $materialId)");
} catch (Exception $e) {
    error_log("Error recording preview: " . $e->getMessage());
    // Continue with preview even if tracking fails
}

// Set headers for file serving
$fileName = $material['file_name'];
$fileSize = filesize($filePath);
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Determine MIME type
$mimeTypes = [
    'pdf' => 'application/pdf',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'txt' => 'text/plain',
    'md' => 'text/plain',
    'html' => 'text/html',
    'htm' => 'text/html',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp'
];

$mimeType = $mimeTypes[strtolower($fileExtension)] ?? 'application/octet-stream';

// Set headers for preview (display in browser)
header('Content-Type: ' . $mimeType);
header('Content-Disposition: inline; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour

// Output file content
readfile($filePath);
exit;
?>
