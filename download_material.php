<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: login.php');
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
    error_log("Unauthorized download attempt by user ID: {$currentUser['id']} (Premium: {$currentUser['is_premium']}, Admin: {$currentUser['is_admin']})");
    
    header('Location: premium.php');
    exit;
}

// Get material ID from URL
$materialId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$materialId) {
    header('Location: courses.php');
    exit;
}

// Get material details
$material = $course->getMaterialById($materialId);
if (!$material) {
    error_log("Material not found for ID: $materialId");
    header('Location: courses.php');
    exit;
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
    header('Location: courses.php');
    exit;
}

// Record user access and increment download count
try {
    $course->recordUserAccess($currentUser['id'], $materialId);
    $course->incrementDownloadCount($materialId);
    
    // Log successful download
    error_log("Material downloaded successfully - User: {$currentUser['id']} ({$currentUser['username']}), Type: $accessType, Material: {$material['title']} (ID: $materialId)");
} catch (Exception $e) {
    error_log("Error recording download: " . $e->getMessage());
    // Continue with download even if tracking fails
}

// Set headers for file download
$fileName = $material['file_name'];
$fileSize = filesize($filePath);
$fileType = $material['file_type'];

// Determine MIME type
$mimeTypes = [
    'pdf' => 'application/pdf',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$mimeType = $mimeTypes[strtolower($fileType)] ?? 'application/octet-stream';

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output file content
readfile($filePath);
exit;
?>
