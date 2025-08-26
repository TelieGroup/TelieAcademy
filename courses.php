<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

// Get all active courses
$courses = $course->getAllCourses(true);

// Set page variables for head component
$pageTitle = "Course Materials - Telie Academy";
$pageDescription = "Access premium course materials including PDFs, PowerPoints, and study guides for web development, JavaScript, React, and more.";

include 'includes/head.php';
?>

<?php include 'includes/header.php'; ?>

<style>
/* Enhanced Course Card Styling */
.course-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    overflow: hidden;
    border-radius: 12px;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.course-card .card-img-top {
    transition: transform 0.3s ease;
}

.course-card:hover .card-img-top {
    transform: scale(1.05);
}

.course-card .card-body {
    padding: 1.5rem;
}

.course-card .card-title {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.course-card .card-text {
    color: #6c757d;
    line-height: 1.6;
    font-size: 0.95rem;
}

.course-card .btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.course-card .btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

/* Course Cover Image Styling */
.course-cover img {
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Course Stats Styling */
.course-stats {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

.stat-item {
    padding: 0.5rem;
}

.stat-item i {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.25rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .course-card .card-body {
        padding: 1rem;
    }
    
    .course-card .card-title {
        font-size: 1.1rem;
    }
    
    .course-card .card-text {
        font-size: 0.9rem;
    }
}
</style>

<!-- Reading Progress Bar -->
<div class="reading-progress-bar" id="readingProgressBar"></div>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-graduation-cap text-primary"></i>
                    Course Materials
                </h1>
                <p class="lead text-muted">
                    Access premium learning resources including PDFs, PowerPoints, and comprehensive study guides
                </p>
                
                <?php if ($isPremium && !empty($courses)): ?>
                    <div class="mt-3">
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            <i class="fas fa-book me-2"></i><?php echo count($courses); ?> Course<?php echo count($courses) !== 1 ? 's' : ''; ?> Available
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if (!$isLoggedIn): ?>
                    <div class="mt-4">
                        <button class="btn btn-primary btn-lg me-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Access
                        </button>
                        <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </div>
                <?php elseif (!$isPremium): ?>
                    <div class="mt-4">
                        <div class="alert alert-warning d-inline-block">
                            <i class="fas fa-crown me-2"></i>
                            <strong>Premium Required:</strong> Upgrade to access course materials
                        </div>
                        <br>
                        <a href="subscription-settings" class="btn btn-warning btn-lg">
                            <i class="fas fa-crown me-2"></i>Upgrade to Premium
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($isPremium): ?>
                <!-- Premium User Content -->
                <div class="row">
                    <?php foreach ($courses as $courseItem): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 course-card">
                                <?php if ($courseItem['thumbnail']): ?>
                                    <img src="<?php echo str_replace('../', '', htmlspecialchars($courseItem['thumbnail'])); ?>" 
                                         class="card-img-top" alt="Course Cover" 
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="fas fa-graduation-cap fa-4x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($courseItem['title']); ?></h5>
                                    <p class="card-text text-muted flex-grow-1">
                                        <?php echo htmlspecialchars(substr($courseItem['description'], 0, 120)) . (strlen($courseItem['description']) > 120 ? '...' : ''); ?>
                                    </p>
                                    
                                    <div class="course-stats mb-3">
                                        <?php
                                        // Get module count for this course
                                        $modules = $course->getModulesByCourse($courseItem['id'], true);
                                        $moduleCount = count($modules);
                                        
                                        // Get total material count
                                        $totalMaterials = 0;
                                        foreach ($modules as $module) {
                                            $materials = $course->getMaterialsByModule($module['id'], true);
                                            $totalMaterials += count($materials);
                                        }
                                        ?>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <i class="fas fa-layer-group text-primary"></i>
                                                    <div class="stat-number"><?php echo $moduleCount; ?></div>
                                                    <div class="stat-label">Modules</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <i class="fas fa-file-alt text-success"></i>
                                                    <div class="stat-number"><?php echo $totalMaterials; ?></div>
                                                    <div class="stat-label">Materials</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            <a href="course?slug=<?php echo $courseItem['slug']; ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-book-open me-2"></i>View Course
                                            </a>
                                            <?php if ($moduleCount > 0 && $totalMaterials > 0): ?>
                                                <a href="course?slug=<?php echo $courseItem['slug']; ?>" 
                                                   class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-eye me-2"></i>Browse Materials
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Non-Premium User Content -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-lock fa-4x text-muted mb-4"></i>
                                <h3>Premium Content Locked</h3>
                                <p class="text-muted mb-4">
                                    Our course materials include comprehensive PDFs, PowerPoint presentations, 
                                    and study guides that complement our tutorials. Upgrade to premium to unlock 
                                    access to all learning resources.
                                </p>
                                
                                <?php if ($isLoggedIn): ?>
                                    <a href="subscription-settings" class="btn btn-warning btn-lg">
                                        <i class="fas fa-crown me-2"></i>Upgrade to Premium
                                    </a>
                                <?php else: ?>
                                    <div class="d-flex gap-3 justify-content-center">
                                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login
                                        </button>
                                        <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                                            <i class="fas fa-user-plus me-2"></i>Register
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/modals.php'; ?>
<?php include 'includes/scripts.php'; ?>
<?php include 'includes/footer.php'; ?>
