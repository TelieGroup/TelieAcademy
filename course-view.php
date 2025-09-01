<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/Post.php';
require_once 'includes/User.php';

$course = new Course();
$post = new Post();
$user = new User();

// Check if user is logged in
$isLoggedIn = $user->isLoggedIn();
$currentUser = $isLoggedIn ? $user->getCurrentUser() : null;

// Get course slug from URL
$courseSlug = isset($_GET['course']) ? $_GET['course'] : '';
if (!$courseSlug) {
    header('Location: courses');
    exit;
}

// Get course details
$courseData = $course->getCourseBySlug($courseSlug);
if (!$courseData) {
    header('Location: courses');
    exit;
}

// Get modules for this course with posts
$modules = $course->getModulesWithPostsByCourse($courseData['id'], true);

// Get user's progress if logged in
$userProgress = [];
$courseEnrollment = null;
$recommendedMaterials = [];
if ($isLoggedIn) {
    $userProgress = $course->getUserCourseProgress($currentUser['id'], $courseData['id']);
    $courseEnrollment = $course->getUserCourseEnrollment($currentUser['id'], $courseData['id']);
    
    // Get recommended materials for this user
    $recommendedMaterials = $course->getRecommendedMaterials($currentUser['id'], $courseData['id'], 6);
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

// Set page variables for head component
$pageTitle = $courseData['title'] . " - Progressive Learning | Telie Academy";
$pageDescription = $courseData['description'] . " Follow a structured learning path with progress tracking.";

include 'includes/head.php';
?>

<style>
/* Enhanced Course Learning Interface */
.course-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 0;
    margin-bottom: 3rem;
}

.progress-ring {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(
        #28a745 0deg,
        #28a745 <?php echo $overallProgress * 3.6; ?>deg,
        rgba(255,255,255,0.2) <?php echo $overallProgress * 3.6; ?>deg,
        rgba(255,255,255,0.2) 360deg
    );
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progress-ring::before {
    content: '';
    width: 90px;
    height: 90px;
    background: white;
    border-radius: 50%;
    position: absolute;
}

.progress-text {
    position: relative;
    z-index: 1;
    font-weight: bold;
    font-size: 1.2rem;
    color: #333;
}

.module-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.module-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.module-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.lesson-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
    position: relative;
}

.lesson-item:last-child {
    border-bottom: none;
}

.lesson-item:hover {
    background-color: #f8f9fa;
}

.lesson-status {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.lesson-status.completed {
    background: #28a745;
    color: white;
}

.lesson-status.current {
    background: #007bff;
    color: white;
    animation: pulse 2s infinite;
}

.lesson-status.locked {
    background: #6c757d;
    color: white;
}

.lesson-status.available {
    background: #fff;
    border: 2px solid #007bff;
    color: #007bff;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}

.lesson-meta {
    font-size: 0.875rem;
    color: #6c757d;
}

.enroll-btn {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    border-radius: 25px;
    padding: 0.8rem 2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.enroll-btn:hover {
    background: linear-gradient(45deg, #20c997, #17a2b8);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
}

.course-stats {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
    display: block;
}

.lesson-preview {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 0.75rem 1rem;
    margin-top: 0.5rem;
    border-radius: 0 8px 8px 0;
    font-size: 0.9rem;
    color: #1976d2;
}

/* Material Recommendation Cards */
.recommended-materials-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 2rem;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.material-recommendation-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.material-recommendation-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    border-color: #007bff;
}

.material-icon-small {
    text-align: center;
    padding: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.material-content-small {
    padding: 1rem;
    flex-grow: 1;
}

.material-title-small {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.material-description-small {
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.4;
    margin-bottom: 0.75rem;
}

.material-meta-small {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.material-footer-small {
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.material-footer-small .btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.material-footer-small .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12);
}

/* Responsive Design */
@media (max-width: 768px) {
    .course-hero {
        padding: 2rem 0;
    }
    
    .progress-ring {
        width: 80px;
        height: 80px;
    }
    
    .progress-ring::before {
        width: 60px;
        height: 60px;
    }
    
    .progress-text {
        font-size: 1rem;
    }
    
    .lesson-item {
        padding: 0.75rem 1rem;
    }
}
</style>

<?php include 'includes/header.php'; ?>

<!-- Reading Progress Bar -->
<div class="reading-progress-bar" id="readingProgressBar"></div>

<!-- Course Hero Section -->
<div class="course-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb text-white-50">
                        <li class="breadcrumb-item"><a href="courses" class="text-white-50">Courses</a></li>
                        <li class="breadcrumb-item active text-white"><?php echo htmlspecialchars($courseData['title']); ?></li>
                    </ol>
                </nav>
                
                <h1 class="display-4 mb-3"><?php echo htmlspecialchars($courseData['title']); ?></h1>
                <p class="lead mb-4"><?php echo htmlspecialchars($courseData['description']); ?></p>
                
                <?php if (!$isLoggedIn): ?>
                    <div class="alert alert-light d-inline-block mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Login Required:</strong> Please login to track your progress and access course features
                    </div>
                    <br>
                    <button type="button" class="btn btn-light btn-lg me-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Start Learning
                    </button>
                <?php elseif (!$courseEnrollment): ?>
                    <form method="POST" action="enroll-course" class="d-inline">
                        <input type="hidden" name="course_id" value="<?php echo $courseData['id']; ?>">
                        <button type="submit" class="btn enroll-btn btn-lg">
                            <i class="fas fa-play me-2"></i>Enroll in Course
                        </button>
                    </form>
                <?php else: ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-calendar me-1"></i>
                            Enrolled <?php echo date('M j, Y', strtotime($courseEnrollment['enrolled_at'])); ?>
                        </span>
                        <?php if ($courseEnrollment['completed_at']): ?>
                            <span class="badge bg-success px-3 py-2">
                                <i class="fas fa-trophy me-1"></i>
                                Completed <?php echo date('M j, Y', strtotime($courseEnrollment['completed_at'])); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4 text-center">
                <div class="progress-ring mx-auto">
                    <div class="progress-text">
                        <?php echo $overallProgress; ?>%
                    </div>
                </div>
                <p class="mt-3 mb-0">Course Progress</p>
                <small class="text-white-50"><?php echo $completedLessons; ?> of <?php echo $totalLessons; ?> lessons completed</small>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Course Modules -->
            <?php if (empty($modules)): ?>
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h3>Course Content Coming Soon</h3>
                        <p class="text-muted">This course is being prepared. Check back later for lessons!</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($modules as $moduleIndex => $module): ?>
                    <div class="module-card">
                        <div class="module-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="mb-1">
                                        <span class="badge bg-primary me-2"><?php echo $module['order_index']; ?></span>
                                        <?php echo htmlspecialchars($module['title']); ?>
                                    </h4>
                                    <p class="mb-0 text-muted"><?php echo htmlspecialchars($module['description']); ?></p>
                                </div>
                                <div class="text-end">
                                    <?php
                                    $moduleProgress = 0;
                                    $moduleLessons = count($module['posts'] ?? []);
                                    $moduleCompleted = 0;
                                    
                                    if (!empty($module['posts'])) {
                                        foreach ($module['posts'] as $modulePost) {
                                            if (isset($userProgress[$modulePost['id']]) && $userProgress[$modulePost['id']]['completed_at']) {
                                                $moduleCompleted++;
                                            }
                                        }
                                        $moduleProgress = $moduleLessons > 0 ? round(($moduleCompleted / $moduleLessons) * 100) : 0;
                                    }
                                    ?>
                                    <small class="text-muted d-block"><?php echo $moduleCompleted; ?>/<?php echo $moduleLessons; ?> lessons</small>
                                    <div class="progress" style="width: 100px; height: 6px;">
                                        <div class="progress-bar" style="width: <?php echo $moduleProgress; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="module-content">
                            <?php if (empty($module['posts'])): ?>
                                <div class="lesson-item text-center text-muted py-4">
                                    <i class="fas fa-hourglass-half fa-2x mb-2"></i>
                                    <p>Lessons coming soon...</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($module['posts'] as $lessonIndex => $lesson): ?>
                                    <?php
                                    $isCompleted = isset($userProgress[$lesson['id']]) && $userProgress[$lesson['id']]['completed_at'];
                                    $isAvailable = $isLoggedIn && ($courseEnrollment || !$lesson['is_premium']);
                                    $isCurrent = false; // Logic to determine current lesson
                                    
                                    // Determine if this is the next lesson to take
                                    if ($isLoggedIn && $courseEnrollment && !$isCompleted) {
                                        $previousLessonsCompleted = true;
                                        foreach ($module['posts'] as $checkIndex => $checkLesson) {
                                            if ($checkIndex < $lessonIndex) {
                                                if (!isset($userProgress[$checkLesson['id']]) || !$userProgress[$checkLesson['id']]['completed_at']) {
                                                    $previousLessonsCompleted = false;
                                                    break;
                                                }
                                            }
                                        }
                                        $isCurrent = $previousLessonsCompleted;
                                    }
                                    ?>
                                    
                                    <div class="lesson-item">
                                        <div class="d-flex align-items-center">
                                            <!-- Lesson Status Icon -->
                                            <div class="lesson-status <?php 
                                                echo $isCompleted ? 'completed' : 
                                                    ($isCurrent ? 'current' : 
                                                    ($isAvailable ? 'available' : 'locked')); 
                                            ?>">
                                                <?php if ($isCompleted): ?>
                                                    <i class="fas fa-check"></i>
                                                <?php elseif ($isCurrent): ?>
                                                    <i class="fas fa-play"></i>
                                                <?php elseif ($isAvailable): ?>
                                                    <i class="fas fa-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-lock"></i>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Lesson Content -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <?php if ($isAvailable): ?>
                                                            <h6 class="mb-1">
                                                                <a href="post?slug=<?php echo $lesson['slug']; ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                                                    <?php if ($lesson['is_premium']): ?>
                                                                        <i class="fas fa-crown text-warning ms-1"></i>
                                                                    <?php endif; ?>
                                                                </a>
                                                            </h6>
                                                        <?php else: ?>
                                                            <h6 class="mb-1 text-muted">
                                                                <?php echo htmlspecialchars($lesson['title']); ?>
                                                                <?php if ($lesson['is_premium']): ?>
                                                                    <i class="fas fa-crown text-warning ms-1"></i>
                                                                <?php endif; ?>
                                                            </h6>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($lesson['excerpt'])): ?>
                                                            <div class="lesson-preview">
                                                                <?php echo substr(strip_tags($lesson['excerpt']), 0, 100) . '...'; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="lesson-meta text-end">
                                                        <small class="d-block">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?php echo $lesson['reading_time'] ?? '5'; ?> min read
                                                        </small>
                                                        <?php if ($isCompleted): ?>
                                                            <small class="text-success">
                                                                <i class="fas fa-check-circle me-1"></i>
                                                                Completed
                                                            </small>
                                                        <?php elseif ($isCurrent): ?>
                                                            <small class="text-primary">
                                                                <i class="fas fa-arrow-right me-1"></i>
                                                                Continue
                                                            </small>
                                                        <?php elseif (!$isAvailable): ?>
                                                            <small class="text-muted">
                                                                <i class="fas fa-lock me-1"></i>
                                                                <?php echo !$isLoggedIn ? 'Login Required' : ($lesson['is_premium'] ? 'Premium' : 'Locked'); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Recommended Materials Section -->
            <?php if ($isLoggedIn && !empty($recommendedMaterials)): ?>
                <div class="recommended-materials-section mt-5">
                    <div class="section-header mb-4">
                        <h4 class="mb-2">
                            <i class="fas fa-star me-2 text-warning"></i>
                            Recommended Materials for You
                        </h4>
                        <p class="text-muted mb-0">Based on your progress, these materials might be helpful</p>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($recommendedMaterials as $material): ?>
                            <div class="col-md-6 mb-3">
                                <div class="material-recommendation-card h-100">
                                    <div class="material-icon-small">
                                        <?php
                                        $iconClass = 'fa-file';
                                        switch (strtolower($material['file_type'])) {
                                            case 'pdf':
                                                $iconClass = 'fa-file-pdf text-danger';
                                                break;
                                            case 'ppt':
                                            case 'pptx':
                                                $iconClass = 'fa-file-powerpoint text-warning';
                                                break;
                                            case 'doc':
                                            case 'docx':
                                                $iconClass = 'fa-file-word text-primary';
                                                break;
                                            default:
                                                $iconClass = 'fa-file-alt text-secondary';
                                        }
                                        ?>
                                        <i class="fas <?php echo $iconClass; ?> fa-2x"></i>
                                    </div>
                                    
                                    <div class="material-content-small">
                                        <h6 class="material-title-small"><?php echo htmlspecialchars($material['title']); ?></h6>
                                        <p class="material-description-small"><?php echo htmlspecialchars(substr($material['description'], 0, 100)) . (strlen($material['description']) > 100 ? '...' : ''); ?></p>
                                        
                                        <div class="material-meta-small">
                                            <small class="text-muted">
                                                <i class="fas fa-download me-1"></i>
                                                <?php echo number_format($material['download_count']); ?> downloads
                                            </small>
                                            <small class="text-muted ms-2">
                                                <i class="fas fa-layer-group me-1"></i>
                                                <?php echo htmlspecialchars($material['module_title']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="material-footer-small">
                                        <a href="download-material?id=<?php echo $material['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Course Statistics -->
            <div class="course-stats">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line me-2"></i>Course Statistics
                </h5>
                
                <div class="row">
                    <div class="col-6 stat-item">
                        <span class="stat-number"><?php echo count($modules); ?></span>
                        <small class="text-muted">Modules</small>
                    </div>
                    <div class="col-6 stat-item">
                        <span class="stat-number"><?php echo $totalLessons; ?></span>
                        <small class="text-muted">Lessons</small>
                    </div>
                    <div class="col-6 stat-item">
                        <span class="stat-number"><?php echo $completedLessons; ?></span>
                        <small class="text-muted">Completed</small>
                    </div>
                    <div class="col-6 stat-item">
                        <span class="stat-number"><?php echo $totalLessons - $completedLessons; ?></span>
                        <small class="text-muted">Remaining</small>
                    </div>
                </div>
                
                <?php if ($isLoggedIn && $courseEnrollment): ?>
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Overall Progress</span>
                            <span class="fw-bold"><?php echo $overallProgress; ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" style="width: <?php echo $overallProgress; ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!$isLoggedIn): ?>
                        <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Track Progress
                        </button>
                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    <?php elseif (!$courseEnrollment): ?>
                        <form method="POST" action="enroll-course">
                            <input type="hidden" name="course_id" value="<?php echo $courseData['id']; ?>">
                            <button type="submit" class="btn enroll-btn w-100 mb-2">
                                <i class="fas fa-play me-2"></i>Enroll Now
                            </button>
                        </form>
                        <a href="courses" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Browse Courses
                        </a>
                    <?php else: ?>
                        <?php if ($overallProgress < 100): ?>
                            <a href="#" class="btn btn-success w-100 mb-2" onclick="continueWherePage()">
                                <i class="fas fa-play me-2"></i>Continue Learning
                            </a>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-trophy fa-3x text-warning mb-2"></i>
                                <h6>Course Completed!</h6>
                                <p class="text-muted small">Congratulations on finishing this course.</p>
                            </div>
                        <?php endif; ?>
                        
                        <a href="courses" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-graduation-cap me-2"></i>Browse More Courses
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Learning Tips -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Learning Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Follow lessons in order for best results
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-code text-primary me-2"></i>
                            Practice coding examples as you go
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-bookmark text-warning me-2"></i>
                            Bookmark important lessons for review
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-comments text-info me-2"></i>
                            Ask questions in the comments
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function continueWherePage() {
    // Find the first incomplete lesson and navigate to it
    const currentLessons = document.querySelectorAll('.lesson-status.current');
    if (currentLessons.length > 0) {
        const lessonLink = currentLessons[0].closest('.lesson-item').querySelector('a');
        if (lessonLink) {
            window.location.href = lessonLink.href;
        }
    } else {
        // Find first available lesson
        const availableLessons = document.querySelectorAll('.lesson-status.available');
        if (availableLessons.length > 0) {
            const lessonLink = availableLessons[0].closest('.lesson-item').querySelector('a');
            if (lessonLink) {
                window.location.href = lessonLink.href;
            }
        }
    }
}

// Auto-scroll to current lesson on page load
document.addEventListener('DOMContentLoaded', function() {
    const currentLesson = document.querySelector('.lesson-status.current');
    if (currentLesson) {
        currentLesson.closest('.lesson-item').scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
});

// Add progress tracking
function trackLessonProgress(postId) {
    if (typeof postId !== 'undefined') {
        fetch('api/track-progress', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                post_id: postId,
                action: 'start_lesson'
            })
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/modals.php'; ?>
<?php include 'includes/scripts.php'; ?>
