<?php
require_once '../config/session.php';
require_once '../includes/Course.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$course = new Course();

// Get course ID from request
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if (!$courseId) {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

try {
    // Get comprehensive analytics for the course
    $analytics = $course->getCourseAnalytics($courseId);
    
    echo json_encode([
        'success' => true,
        'analytics' => $analytics
    ]);
} catch (Exception $e) {
    error_log("Course analytics error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error loading analytics data'
    ]);
}
?>

