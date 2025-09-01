<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    echo "Please log in to debug progress.\n";
    exit;
}

$currentUser = $user->getCurrentUser();
$courseSlug = 'javascript-learning-journey';

// Get course details
$courseData = $course->getCourseBySlug($courseSlug);
if (!$courseData) {
    echo "JavaScript Learning Journey course not found. Please run setup_javascript_course.php first.\n";
    exit;
}

echo "🔍 Debugging Course Progress for User: {$currentUser['username']}\n";
echo "==========================================\n\n";

echo "📚 Course: {$courseData['title']} (ID: {$courseData['id']})\n\n";

// Get modules and posts
$modules = $course->getModulesWithPostsByCourse($courseData['id'], true);
echo "📖 Modules and Posts:\n";
$totalLessons = 0;
foreach ($modules as $index => $module) {
    $postCount = count($module['posts'] ?? []);
    $totalLessons += $postCount;
    echo "   " . ($index + 1) . ". {$module['title']} (ID: {$module['id']}) - $postCount posts\n";
    
    if (!empty($module['posts'])) {
        foreach ($module['posts'] as $postIndex => $modulePost) {
            echo "      " . ($postIndex + 1) . ". {$modulePost['title']} (Post ID: {$modulePost['id']}, Module ID: {$modulePost['course_module_id']})\n";
        }
    }
}

echo "\n📊 Total Lessons: $totalLessons\n\n";

// Get user's progress
$userProgress = $course->getUserCourseProgress($currentUser['id'], $courseData['id']);
echo "🎯 User Progress Data:\n";
if (empty($userProgress)) {
    echo "   No progress data found.\n";
} else {
    foreach ($userProgress as $postId => $progress) {
        echo "   Post ID $postId: {$progress['progress_percentage']}% ";
        if ($progress['completed_at']) {
            echo "(Completed: {$progress['completed_at']})";
        } else {
            echo "(In Progress)";
        }
        echo " - Time: {$progress['time_spent_minutes']} min\n";
    }
}

// Calculate completion
$completedLessons = 0;
echo "\n✅ Completion Check:\n";
foreach ($modules as $module) {
    if (!empty($module['posts'])) {
        foreach ($module['posts'] as $modulePost) {
            $isCompleted = isset($userProgress[$modulePost['id']]) && $userProgress[$modulePost['id']]['completed_at'];
            echo "   Post '{$modulePost['title']}' (ID: {$modulePost['id']}): " . ($isCompleted ? "✅ COMPLETED" : "❌ NOT COMPLETED") . "\n";
            if ($isCompleted) {
                $completedLessons++;
            }
        }
    }
}

$overallProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 1) : 0;

echo "\n📈 Final Calculation:\n";
echo "   Completed Lessons: $completedLessons\n";
echo "   Total Lessons: $totalLessons\n";
echo "   Overall Progress: $overallProgress%\n\n";

// Check enrollment
$courseEnrollment = $course->getUserCourseEnrollment($currentUser['id'], $courseData['id']);
echo "🎓 Enrollment Status:\n";
if ($courseEnrollment) {
    echo "   ✅ Enrolled on: {$courseEnrollment['enrolled_at']}\n";
    if ($courseEnrollment['completed_at']) {
        echo "   🏆 Course completed on: {$courseEnrollment['completed_at']}\n";
    } else {
        echo "   📚 Course in progress\n";
    }
} else {
    echo "   ❌ Not enrolled in this course\n";
}

echo "\n🔧 Debug Actions:\n";
echo "1. If no posts are linked to modules, run: setup_javascript_course.php\n";
echo "2. If progress isn't tracking, check browser console for API errors\n";
echo "3. If completion isn't working, try reading a full post to 90%+ scroll\n";
echo "4. Check that posts have course_module_id set properly\n\n";

// Show database queries for manual checking
echo "🗄️  Manual Database Queries:\n";
echo "Check posts linked to course:\n";
echo "   SELECT p.id, p.title, p.course_module_id FROM posts p WHERE p.course_module_id IS NOT NULL;\n\n";

echo "Check user progress:\n";
echo "   SELECT * FROM course_progress WHERE user_id = {$currentUser['id']} AND course_id = {$courseData['id']};\n\n";

echo "Check enrollments:\n";
echo "   SELECT * FROM course_enrollments WHERE user_id = {$currentUser['id']} AND course_id = {$courseData['id']};\n\n";
?>
