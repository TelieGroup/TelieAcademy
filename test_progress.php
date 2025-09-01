<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    echo "Please log in to test progress.\n";
    exit;
}

$currentUser = $user->getCurrentUser();

// Get JavaScript course
$courseData = $course->getCourseBySlug('javascript-learning-journey');
if (!$courseData) {
    echo "JavaScript course not found. Run setup_javascript_course.php first.\n";
    exit;
}

echo "🧪 Testing Progress for User: {$currentUser['username']}\n";
echo "===============================================\n\n";

// Auto-enroll user if not enrolled
$enrollment = $course->getUserCourseEnrollment($currentUser['id'], $courseData['id']);
if (!$enrollment) {
    echo "📝 Auto-enrolling user in JavaScript course...\n";
    $course->enrollUserInCourse($currentUser['id'], $courseData['id']);
    echo "✅ User enrolled successfully!\n\n";
}

// Get modules with posts
$modules = $course->getModulesWithPostsByCourse($courseData['id'], true);

if (empty($modules)) {
    echo "❌ No modules found. Run setup_javascript_course.php first.\n";
    exit;
}

// Find a post to mark as completed for testing
$testPost = null;
$testModule = null;

foreach ($modules as $module) {
    if (!empty($module['posts'])) {
        $testPost = $module['posts'][0]; // Get first post
        $testModule = $module;
        break;
    }
}

if (!$testPost) {
    echo "❌ No posts found in course modules. Run setup_javascript_course.php first.\n";
    exit;
}

echo "🎯 Testing with post: '{$testPost['title']}'\n";
echo "   Post ID: {$testPost['id']}\n";
echo "   Module: {$testModule['title']} (ID: {$testModule['id']})\n\n";

// Test progress tracking
echo "📊 Testing progress tracking...\n";

// Test 1: Start lesson
echo "1. Starting lesson...\n";
$result = $course->trackLessonProgress($currentUser['id'], $courseData['id'], $testModule['id'], $testPost['id'], 0, 0);
echo "   Result: " . ($result ? "✅ Success" : "❌ Failed") . "\n\n";

// Test 2: Update progress to 50%
echo "2. Updating progress to 50%...\n";
$result = $course->trackLessonProgress($currentUser['id'], $courseData['id'], $testModule['id'], $testPost['id'], 50, 2);
echo "   Result: " . ($result ? "✅ Success" : "❌ Failed") . "\n\n";

// Test 3: Complete lesson
echo "3. Completing lesson...\n";
$result = $course->completeLesson($currentUser['id'], $courseData['id'], $testModule['id'], $testPost['id']);
echo "   Result: " . ($result ? "✅ Success" : "❌ Failed") . "\n\n";

// Check progress
echo "📈 Checking updated progress...\n";
$userProgress = $course->getUserCourseProgress($currentUser['id'], $courseData['id']);

if (isset($userProgress[$testPost['id']])) {
    $progress = $userProgress[$testPost['id']];
    echo "   Progress: {$progress['progress_percentage']}%\n";
    echo "   Completed: " . ($progress['completed_at'] ? "✅ Yes ({$progress['completed_at']})" : "❌ No") . "\n";
    echo "   Time spent: {$progress['time_spent_minutes']} minutes\n\n";
} else {
    echo "   ❌ No progress data found\n\n";
}

// Calculate overall course progress
$totalLessons = 0;
$completedLessons = 0;
foreach ($modules as $module) {
    if (!empty($module['posts'])) {
        $totalLessons += count($module['posts']);
        foreach ($module['posts'] as $modulePost) {
            if (isset($userProgress[$modulePost['id']]) && $userProgress[$modulePost['id']]['completed_at']) {
                $completedLessons++;
            }
        }
    }
}

$overallProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 1) : 0;

echo "🎯 Overall Course Progress:\n";
echo "   Completed: $completedLessons / $totalLessons lessons\n";
echo "   Progress: $overallProgress%\n\n";

echo "🔗 Now visit: /course-view?course=javascript-learning-journey\n";
echo "   You should see the updated progress!\n\n";

echo "💡 To test more:\n";
echo "1. Visit a course post and scroll to 90% to auto-complete\n";
echo "2. Check browser console for API calls\n";
echo "3. Use debug_progress.php for detailed debugging\n";
?>
