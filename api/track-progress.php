<?php
require_once '../config/session.php';
require_once '../includes/Course.php';
require_once '../includes/Post.php';
require_once '../includes/User.php';

// Set JSON header
header('Content-Type: application/json');

$course = new Course();
$post = new Post();
$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$currentUser = $user->getCurrentUser();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$postId = isset($input['post_id']) ? (int)$input['post_id'] : 0;
$action = isset($input['action']) ? $input['action'] : '';
$progressPercentage = isset($input['progress_percentage']) ? (float)$input['progress_percentage'] : 0;
$timeSpent = isset($input['time_spent']) ? (int)$input['time_spent'] : 0;

if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

// Get post data to find course and module
$postData = $post->getPostById($postId);
if (!$postData) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

$moduleId = $postData['course_module_id'];
if (!$moduleId) {
    echo json_encode(['success' => false, 'message' => 'Post is not part of a course']);
    exit;
}

// Get module data to find course
$moduleData = $course->getModuleById($moduleId);
$courseId = $moduleData ? $moduleData['course_id'] : null;

if (!$courseId) {
    echo json_encode(['success' => false, 'message' => 'Course not found']);
    exit;
}

// Check if user is enrolled in the course
$enrollment = $course->getUserCourseEnrollment($currentUser['id'], $courseId);
if (!$enrollment) {
    echo json_encode(['success' => false, 'message' => 'User not enrolled in course']);
    exit;
}

try {
    switch ($action) {
        case 'start_lesson':
            // Track that user started the lesson
            $result = $course->trackLessonProgress($currentUser['id'], $courseId, $moduleId, $postId, 0, 0);
            echo json_encode(['success' => $result, 'message' => $result ? 'Lesson started' : 'Failed to track lesson start']);
            break;
            
        case 'update_progress':
            // Update progress percentage
            $result = $course->trackLessonProgress($currentUser['id'], $courseId, $moduleId, $postId, $progressPercentage, $timeSpent);
            echo json_encode(['success' => $result, 'message' => $result ? 'Progress updated' : 'Failed to update progress']);
            break;
            
        case 'complete_lesson':
            // Mark lesson as completed
            $result = $course->completeLesson($currentUser['id'], $courseId, $moduleId, $postId);
            echo json_encode(['success' => $result, 'message' => $result ? 'Lesson completed' : 'Failed to complete lesson']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Progress tracking error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
