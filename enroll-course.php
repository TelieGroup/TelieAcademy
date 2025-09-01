<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: index?error=login_required');
    exit;
}

$currentUser = $user->getCurrentUser();

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
    
    if ($courseId <= 0) {
        header('Location: courses?error=invalid_course');
        exit;
    }
    
    // Check if course exists
    $courseData = $course->getCourseById($courseId);
    if (!$courseData) {
        header('Location: courses?error=course_not_found');
        exit;
    }
    
    // Check if already enrolled
    $enrollment = $course->getUserCourseEnrollment($currentUser['id'], $courseId);
    if ($enrollment) {
        header('Location: course-view?course=' . $courseData['slug'] . '&message=already_enrolled');
        exit;
    }
    
    // Enroll user
    $enrollmentResult = $course->enrollUserInCourse($currentUser['id'], $courseId);
    
    if ($enrollmentResult) {
        header('Location: course-view?course=' . $courseData['slug'] . '&message=enrollment_success');
    } else {
        header('Location: course-view?course=' . $courseData['slug'] . '&error=enrollment_failed');
    }
    exit;
} else {
    // Invalid request method
    header('Location: courses');
    exit;
}
?>
