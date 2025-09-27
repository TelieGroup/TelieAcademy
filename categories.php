<?php
require_once 'includes/Post.php';
require_once 'includes/Category.php';
require_once 'includes/User.php';
require_once 'includes/Tag.php'; // Added Tag.php
require_once 'includes/Course.php'; // Added Course.php

// Initialize classes
$post = new Post();
$category = new Category();
$user = new User();
$tag = new Tag(); // Initialize Tag class
$course = new Course(); // Initialize Course class

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$currentUser = $isLoggedIn ? $user->getCurrentUser() : null;
$isPremium = $user->isPremium();

// Get selected category from URL
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Get all categories
$categories = $category->getAllCategories();

// Get posts for selected category or all posts
if ($selectedCategory) {
    $posts = $post->getPostsByCategory($selectedCategory, $isPremium);
    $currentCategory = $category->getCategoryWithPostCount($selectedCategory);
} else {
    $posts = $post->getAllPosts(null, 0, $isPremium);
    $currentCategory = null;
}

// Enhance posts with course information
if ($isLoggedIn && !empty($posts)) {
    foreach ($posts as &$postItem) {
        if (!empty($postItem['course_module_id'])) {
            // Get course context
            $moduleInfo = $course->getModuleById($postItem['course_module_id']);
            if ($moduleInfo) {
                $courseInfo = $course->getCourseById($moduleInfo['course_id']);
                $postItem['course_context'] = $courseInfo;
                $postItem['module_info'] = $moduleInfo;
                
                // Get user progress for this post
                $userProgress = $course->getUserCourseProgress($currentUser['id'], $courseInfo['id']);
                $postItem['user_progress'] = isset($userProgress[$postItem['id']]) ? $userProgress[$postItem['id']] : null;
            }
        }
    }
}

// Get available courses for this category
$availableCourses = [];
if ($selectedCategory && $isLoggedIn) {
    $availableCourses = $course->getCoursesWithPostsInCategory($selectedCategory, $currentUser['id']);
}

// Set page variables for head component
$pageTitle = $currentCategory ? htmlspecialchars($currentCategory['name']) : 'Academic Disciplines';
$pageDescription = 'Explore learning materials organized by academic disciplines and subject areas.';

include 'includes/head.php';
?>
    <?php include 'includes/header.php'; ?>

    <!-- Header Section -->
    <section class="py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <?php if ($currentCategory): ?>
                            <?php echo htmlspecialchars($currentCategory['name']); ?> Learning Materials
                        <?php else: ?>
                            Academic Disciplines
                        <?php endif; ?>
                    </h1>
                    <?php if ($currentCategory): ?>
                    <p class="lead text-muted"><?php echo htmlspecialchars($currentCategory['description']); ?></p>
                    <p class="text-muted"><?php echo $currentCategory['post_count']; ?> learning resources available</p>
                    <?php else: ?>
                    <p class="lead text-muted">Explore educational content organized by academic disciplines and subject areas</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter Buttons -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                                 <a href="categories" class="btn <?php echo !$selectedCategory ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All Disciplines
                        </a>
                        <?php foreach ($categories as $cat): ?>
                                                 <a href="categories?category=<?php echo $cat['slug']; ?>" 
                           class="btn <?php echo $selectedCategory === $cat['slug'] ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                            <span class="badge bg-light text-dark ms-1"><?php echo $cat['post_count']; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Learning Insights Section -->
    <?php if ($isLoggedIn && $selectedCategory && !empty($availableCourses)): ?>
    <section class="learning-insights-section py-4 bg-primary bg-opacity-10">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Your Academic Progress in <?php echo htmlspecialchars($currentCategory['name']); ?>
                    </h5>
                    <div class="learning-stats">
                        <?php
                        $totalCategoryPosts = 0;
                        $completedCategoryPosts = 0;
                        $inProgressCategoryPosts = 0;
                        
                        foreach ($availableCourses as $courseItem) {
                            $userProgress = $course->getUserCourseProgress($currentUser['id'], $courseItem['id']);
                            if (!empty($userProgress)) {
                                foreach ($userProgress as $lesson) {
                                    // Check if this lesson belongs to the current category
                                    $lessonPost = $post->getPostById($lesson['post_id']);
                                    if ($lessonPost && $lessonPost['category_slug'] === $selectedCategory) {
                                        $totalCategoryPosts++;
                                        if ($lesson['progress_percentage'] == 100) {
                                            $completedCategoryPosts++;
                                        } elseif ($lesson['progress_percentage'] > 0) {
                                            $inProgressCategoryPosts++;
                                        }
                                    }
                                }
                            }
                        }
                        
                        $categoryProgress = $totalCategoryPosts > 0 ? round(($completedCategoryPosts / $totalCategoryPosts) * 100) : 0;
                        ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="insight-card">
                                    <div class="insight-icon">
                                        <i class="fas fa-book-open text-primary"></i>
                                    </div>
                                    <div class="insight-content">
                                        <div class="insight-number"><?php echo $totalCategoryPosts; ?></div>
                                        <div class="insight-label">Academic Modules</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="insight-card">
                                    <div class="insight-icon">
                                        <i class="fas fa-check-circle text-success"></i>
                                    </div>
                                    <div class="insight-content">
                                        <div class="insight-number"><?php echo $completedCategoryPosts; ?></div>
                                        <div class="insight-label">Modules Completed</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="insight-card">
                                    <div class="insight-icon">
                                        <i class="fas fa-percentage text-warning"></i>
                                    </div>
                                    <div class="insight-content">
                                        <div class="insight-number"><?php echo $categoryProgress; ?>%</div>
                                        <div class="insight-label">Academic Achievement</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($categoryProgress > 0): ?>
                        <div class="category-progress-bar mt-3">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" style="width: <?php echo $categoryProgress; ?>%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block text-center">
                                <?php echo $completedCategoryPosts; ?> of <?php echo $totalCategoryPosts; ?> modules completed
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="learning-actions">
                        <h6 class="mb-3">Academic Dashboard</h6>
                        <a href="profile" class="btn btn-outline-primary btn-sm mb-2 w-100">
                            <i class="fas fa-user-graduate me-1"></i>View My Progress
                        </a>
                        <a href="courses" class="btn btn-outline-success btn-sm mb-2 w-100">
                            <i class="fas fa-list me-1"></i>Explore All Programs
                        </a>
                        <?php if ($inProgressCategoryPosts > 0): ?>
                            <a href="posts" class="btn btn-outline-warning btn-sm w-100">
                                <i class="fas fa-play me-1"></i>Resume Studies
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Banner Ads Section -->
    <section class="py-4" style="display: none;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="ad-banner-container">
                        <div class="ad-banner" id="categories-banner-ad">
                            <!-- Google AdSense Banner Ad Placeholder -->
                            <div class="ad-placeholder">
                                <div class="ad-content">
                                    <i class="fas fa-ad fa-2x text-muted mb-2"></i>
                                    <h6 class="text-muted">Advertisement</h6>
                                    <p class="text-muted small">Google AdSense Banner Ad</p>
                                    <div class="ad-dimensions">
                                        <span class="badge bg-light text-dark">728x90</span>
                                        <span class="badge bg-light text-dark">300x250</span>
                                        <span class="badge bg-light text-dark">320x50</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Posts Section -->
    <section class="py-5">
        <div class="container">
            <div class="row" id="postsContainer">
                <?php if (empty($posts)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">No learning materials found</h3>
                    <p class="text-muted">No educational content available in this discipline yet.</p>
                    <a href="categories" class="btn btn-primary">Browse All Disciplines</a>
                </div>
                <?php else: ?>
                <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                
                                <!-- Course Badges -->
                                <?php if (isset($post['course_context'])): ?>
                                    <span class="course-badge-modern">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        <?php echo htmlspecialchars($post['course_context']['title']); ?>
                                    </span>
                                    <span class="module-badge-modern">
                                        <i class="fas fa-layer-group me-1"></i>
                                        <?php echo htmlspecialchars($post['module_info']['title']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($post['is_premium']): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-crown me-1"></i>Premium
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="card-title">
                                <a href="post?slug=<?php echo $post['slug']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h5>
                            
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($post['excerpt']); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($post['author_name']); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($post['published_at'])); ?>
                                </small>
                            </div>
                            
                            <?php if ($post['tags']): ?>
                            <div class="mt-3">
                                <?php 
                                $tags = explode(',', $post['tags']);
                                foreach (array_slice($tags, 0, 3) as $tagItem):
                                    $tagName = trim($tagItem);
                                    if (!empty($tagName)):
                                        // Get tag properties from database
                                        $tagData = $tag->getTagByName($tagName);
                                        $tagColor = $tagData ? $tagData['color'] : '#6c757d';
                                        $tagSlug = $tagData ? $tagData['slug'] : strtolower(str_replace(' ', '-', $tagName));
                                ?>
                                <a href="tags?tag=<?php echo urlencode($tagSlug); ?>" class="badge text-decoration-none me-1" style="background-color: <?php echo htmlspecialchars($tagColor); ?>; color: white;">
                                    <?php echo htmlspecialchars($tagName); ?>
                                </a>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                                <?php if (count($tags) > 3): ?>
                                <span class="badge bg-light text-dark">+<?php echo count($tags) - 3; ?> more</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Learning Progress Section -->
                            <?php if (isset($post['course_context']) && isset($post['user_progress'])): ?>
                            <div class="learning-progress-section mt-3">
                                <div class="progress-header">
                                    <small class="text-muted">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Your Academic Progress
                                    </small>
                                    <small class="progress-percentage">
                                        <?php echo $post['user_progress']['progress_percentage'] ?? 0; ?>%
                                    </small>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: <?php echo $post['user_progress']['progress_percentage'] ?? 0; ?>%">
                                    </div>
                                </div>
                                <?php if ($post['user_progress']['completed_at']): ?>
                                    <small class="text-success mt-1 d-block">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Completed on <?php echo date('M j, Y', strtotime($post['user_progress']['completed_at'])); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <?php elseif (isset($post['course_context'])): ?>
                            <div class="learning-status-section mt-3">
                                <div class="status-info">
                                    <small class="text-primary">
                                        <i class="fas fa-play-circle me-1"></i>
                                        Part of <?php echo htmlspecialchars($post['course_context']['title']); ?> program
                                    </small>
                                    <?php if ($isLoggedIn): ?>
                                        <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-play me-1"></i>Begin Studies
                                        </a>
                                    <?php else: ?>
                                        <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                            <i class="fas fa-eye me-1"></i>View Material
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h3 class="mb-4">Stay Connected with Telie Academy</h3>
                    <p class="text-muted mb-4">Get notified when we publish new learning materials in your preferred academic disciplines.</p>
                    <div class="newsletter-form">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" id="newsletterEmail" placeholder="Enter your email address">
                            <button class="btn btn-primary" type="button" id="newsletterSubmit">
                                <i class="fas fa-paper-plane me-1"></i>Subscribe
                            </button>
                        </div>
                        <div id="newsletterMessage" class="alert" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/modals.php'; ?>

    <style>
    /* Enhanced Post Card Badges */
    .course-badge-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-block;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .module-badge-modern {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-block;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    /* Learning Progress Section */
    .learning-progress-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e9ecef;
    }
    
    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .progress-percentage {
        font-weight: 600;
        color: #28a745;
    }
    
    .learning-progress-section .progress {
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .learning-progress-section .progress-bar {
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        transition: width 0.6s ease;
    }
    
    /* Learning Status Section */
    .learning-status-section {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #90caf9;
        text-align: center;
    }
    
    .status-info .btn {
        border-radius: 20px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }
    
    .status-info .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    }
    
    /* Learning Insights Section */
    .learning-insights-section {
        border-top: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
    }
    
    .insight-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .insight-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }
    
    .insight-icon {
        margin-bottom: 1rem;
    }
    
    .insight-icon i {
        font-size: 2rem;
    }
    
    .insight-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .insight-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .category-progress-bar {
        background: #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        padding: 1rem;
    }
    
    .category-progress-bar .progress {
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .category-progress-bar .progress-bar {
        background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
        transition: width 0.6s ease;
    }
    
    .learning-actions {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    
    .learning-actions h6 {
        color: #2d3748;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
    }
    
    .learning-actions .btn {
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }
    
    .learning-actions .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .course-badge-modern,
        .module-badge-modern {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .insight-card {
            margin-bottom: 1rem;
        }
        
        .learning-actions {
            margin-top: 1rem;
        }
    }
    </style>

    <?php include 'includes/scripts.php'; ?> 