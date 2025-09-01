<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: index?login_required=1');
    exit;
}

$currentUser = $user->getCurrentUser();
$materialId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$materialId) {
    header('Location: courses');
    exit;
}

// Get material details
$material = $course->getMaterialById($materialId);
if (!$material) {
    header('Location: courses?error=material_not_found');
    exit;
}

// Check if user can access this material
if (!$course->canAccessMaterial($currentUser['id'], $materialId)) {
    // Get required lesson info for error message
    $requiredLessonId = $material['required_lesson_id'];
    $requiredLessonTitle = '';
    
    if ($requiredLessonId) {
        // Get the required lesson title
        require_once 'includes/Post.php';
        $post = new Post();
        $requiredLesson = $post->getPostById($requiredLessonId);
        $requiredLessonTitle = $requiredLesson ? $requiredLesson['title'] : 'a specific lesson';
    }
    
    $errorMessage = $requiredLessonId ? 
        "You must complete \"$requiredLessonTitle\" before accessing this material." :
        "You don't have access to this material.";
    
    header("Location: course-view?course=" . urlencode($material['course_title']) . "&error=" . urlencode($errorMessage));
    exit;
}

// Check if file exists
if (!file_exists($material['file_path'])) {
    header('Location: courses?error=file_not_found');
    exit;
}

// Track the material access
$course->trackMaterialAccess($currentUser['id'], $materialId);

// Set headers for file download
$fileSize = filesize($material['file_path']);
$fileName = $material['file_name'];

// Clean the filename for download
$downloadFileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output file content
readfile($material['file_path']);
exit;
?>

