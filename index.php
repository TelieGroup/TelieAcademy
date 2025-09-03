<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/Category.php';
require_once 'includes/Tag.php';
require_once 'includes/User.php';
require_once 'includes/Vote.php';
require_once 'includes/Course.php';

// Initialize classes
$post = new Post();
$category = new Category();
$tag = new Tag();
$user = new User();
$vote = new Vote();
$course = new Course();

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$currentUser = $isLoggedIn ? $user->getCurrentUser() : null;
$isPremium = $currentUser && isset($currentUser['is_premium']) && $currentUser['is_premium'] ? true : false;

// Get sort parameter
$sortBy = $_GET['sort'] ?? 'date';

// Get featured posts
$featuredPosts = $post->getFeaturedPosts(3, $isPremium);

// Get trending posts (posts with high vote scores in recent time)
$trendingPosts = $vote->getTrendingPosts(6, $isPremium);

// Enhance posts with course information
if ($isLoggedIn && !empty($featuredPosts)) {
    foreach ($featuredPosts as &$postItem) {
        if (!empty($postItem['course_module_id'])) {
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

if ($isLoggedIn && !empty($trendingPosts)) {
    foreach ($trendingPosts as &$postItem) {
        if (!empty($postItem['course_module_id'])) {
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

// Get all categories
$categories = $category->getAllCategories();

// Get popular tags
$popularTags = $tag->getPopularTags(10);

// Get total post count
$totalPosts = $post->getPostCount($isPremium);

// Get courses with progress for homepage
$coursesWithProgress = $course->getCoursesWithProgress($isLoggedIn ? $currentUser['id'] : null);

// Set page variables for head component
$pageTitle = 'Home';
$pageDescription = 'Learn modern web development, JavaScript, React, Python and more with our comprehensive tutorials.';

include 'includes/head.php';
?>
    <?php include 'includes/header.php'; ?>

    <!-- Alert Messages -->
    <?php if (isset($_GET['login_required'])): ?>
        <div class="container mt-3">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-lock me-2"></i>
                <strong>Login Required:</strong> You must be logged in to access this content.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['premium_required'])): ?>
        <div class="container mt-3">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-crown me-2"></i>
                <strong>Premium Access Required:</strong> This content is only available to premium users. 
                <?php if ($isLoggedIn): ?>
                    <a href="#" class="alert-link" data-bs-toggle="modal" data-bs-target="#newsletterModal">Upgrade to Premium</a> to access all materials and features.
                <?php else: ?>
                    <a href="#" class="alert-link" data-bs-toggle="modal" data-bs-target="#newsletterModal">Subscribe to Premium</a> to access all materials and features.
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
<section class="hero-section-modern text-center text-white pt-5">
        <div class="container">
            <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="hero-content-modern">
                    <div class="hero-icon-modern mb-4">
                        <i class="fas fa-graduation-cap"></i>
                        </div>
                    <h1 class="hero-title-modern mb-4">
                        <span class="text-gradient">Welcome to Telie Academy</span>
                    </h1>
                    <p class="hero-subtitle-modern mb-5">
                        Your gateway to premium learning materials, comprehensive courses, and expert tutorials. Access exclusive content designed to accelerate your learning journey.
                    </p>
                    
                    <div class="hero-actions-modern mb-5">
                        <?php if (!empty($coursesWithProgress)): ?>
                            <a href="courses" class="btn btn-primary btn-lg me-3 hero-btn-primary">
                                <i class="fas fa-graduation-cap me-2"></i>Explore Courses
                            </a>
                            <a href="#featured-posts" class="btn btn-outline-light btn-lg hero-btn-secondary">
                                <i class="fas fa-star me-2"></i>Featured Content
                            </a>
                        <?php else: ?>
                            <a href="courses" class="btn btn-primary btn-lg me-3 hero-btn-primary">
                                <i class="fas fa-graduation-cap me-2"></i>Browse Courses
                            </a>
                            <a href="#featured-posts" class="btn btn-outline-light btn-lg hero-btn-secondary">
                                <i class="fas fa-star me-2"></i>Featured Content
                            </a>
                        <?php endif; ?>
                        </div>
                        
                    <!-- Search Bar -->
                    <div class="hero-search-container mb-5">
                        <form action="search" method="GET" class="hero-search-form">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" name="q" placeholder="Search courses, materials, and tutorials..." aria-label="Search courses, materials, and tutorials">
                                <button class="btn btn-primary btn-lg" type="submit">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="hero-stats-modern">
                            <div class="row justify-content-center">
                            <div class="col-md-3">
                                <div class="stat-item-modern">
                                    <div class="stat-number-modern"><?php echo $totalPosts; ?>+</div>
                                    <div class="stat-label-modern">Learning Resources</div>
                                </div>
                                    </div>
                            <div class="col-md-3">
                                <div class="stat-item-modern">
                                    <div class="stat-number-modern"><?php echo count($categories); ?></div>
                                    <div class="stat-label-modern">Subject Areas</div>
                                </div>
                                    </div>
                            <div class="col-md-3">
                                <div class="stat-item-modern">
                                    <div class="stat-number-modern"><?php echo count($popularTags); ?></div>
                                    <div class="stat-label-modern">Learning Topics</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item-modern">
                                    <div class="stat-number-modern"><?php echo $isPremium ? 'Premium' : 'Free'; ?></div>
                                    <div class="stat-label-modern">Membership</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Learning Paths Section -->
    <?php if (!empty($coursesWithProgress)): ?>
    <section class="learning-paths-section py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title-modern">
                    <i class="fas fa-road me-3 text-primary"></i>Your Learning Journey
                </h2>
                <p class="section-subtitle-modern">Structured academic programs designed for your educational advancement</p>
            </div>
            
            <div class="row">
                <?php foreach (array_slice($coursesWithProgress, 0, 3) as $courseItem): ?>
                    <?php
                    $courseProgress = 0;
                    if ($isLoggedIn && isset($courseItem['completed_lessons']) && $courseItem['total_lessons'] > 0) {
                        $courseProgress = round(($courseItem['completed_lessons'] / $courseItem['total_lessons']) * 100, 1);
                    }
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="course-card h-100">
                            <div class="course-card-header">
                                <div class="course-progress-ring" data-progress="<?php echo $courseProgress; ?>">
                                    <div class="progress-value"><?php echo $courseProgress; ?>%</div>
                                </div>
                                <div class="course-meta">
                                    <span class="course-modules"><?php echo $courseItem['total_modules']; ?> modules</span>
                                    <span class="course-lessons"><?php echo $courseItem['total_lessons']; ?> lessons</span>
                                </div>
                            </div>
                            
                            <div class="course-card-body">
                                <h5 class="course-title"><?php echo htmlspecialchars($courseItem['title']); ?></h5>
                                <p class="course-description"><?php echo htmlspecialchars(substr($courseItem['description'], 0, 120)) . '...'; ?></p>
                                
                                <?php if ($isLoggedIn): ?>
                                    <?php if (isset($courseItem['enrolled_at'])): ?>
                                        <div class="enrollment-status mb-3">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <span class="text-success">Enrolled</span>
                                            <?php if ($courseItem['course_completed_at']): ?>
                                                <i class="fas fa-trophy text-warning ms-2"></i>
                                                <span class="text-warning">Completed</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="course-stats">
                                    <?php if ($isLoggedIn && isset($courseItem['completed_lessons'])): ?>
                                        <span class="stat-item">
                                            <i class="fas fa-chart-line me-1"></i>
                                            <?php echo $courseItem['completed_lessons']; ?>/<?php echo $courseItem['total_lessons']; ?> completed
                                        </span>
                                    <?php else: ?>
                                        <span class="stat-item">
                                            <i class="fas fa-book me-1"></i>
                                            <?php echo $courseItem['total_lessons']; ?> lessons
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="course-card-footer">
                                <a href="course-view?course=<?php echo $courseItem['slug']; ?>" class="btn btn-primary w-100">
                                    <?php if ($isLoggedIn && isset($courseItem['enrolled_at'])): ?>
                                        <?php if ($courseProgress > 0): ?>
                                            <i class="fas fa-play me-2"></i>Continue Learning
                                        <?php else: ?>
                                            <i class="fas fa-rocket me-2"></i>Start Course
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <i class="fas fa-eye me-2"></i>View Course
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($coursesWithProgress) > 3): ?>
                <div class="text-center mt-4">
                    <a href="courses" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-graduation-cap me-2"></i>View All Courses
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Learning Progress Overview Section -->
    <?php if ($isLoggedIn && !empty($coursesWithProgress)): ?>
    <section class="learning-progress-overview py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Your Academic Progress
                    </h5>
                    <div class="progress-overview-grid">
                        <?php
                        $totalLessons = 0;
                        $completedLessons = 0;
                        $inProgressLessons = 0;
                        
                        foreach ($coursesWithProgress as $courseItem) {
                            $userProgress = $course->getUserCourseProgress($currentUser['id'], $courseItem['id']);
                            $courseTotal = count($userProgress);
                            $courseCompleted = count(array_filter($userProgress, function($p) { return $p['progress_percentage'] == 100; }));
                            $courseInProgress = count(array_filter($userProgress, function($p) { return $p['progress_percentage'] > 0 && $p['progress_percentage'] < 100; }));
                            
                            $totalLessons += $courseTotal;
                            $completedLessons += $courseCompleted;
                            $inProgressLessons += $courseInProgress;
                        }
                        
                        $overallProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                        ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="progress-stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-book-open text-primary"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number"><?php echo $totalLessons; ?></div>
                                        <div class="stat-label">Total Modules</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="progress-stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-check-circle text-success"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number"><?php echo $completedLessons; ?></div>
                                        <div class="stat-label">Modules Completed</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="progress-stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-percentage text-warning"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number"><?php echo $overallProgress; ?>%</div>
                                        <div class="stat-label">Academic Achievement</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($overallProgress > 0): ?>
                        <div class="overall-progress-bar mt-3">
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-primary" style="width: <?php echo $overallProgress; ?>%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block text-center">
                                <?php echo $completedLessons; ?> of <?php echo $totalLessons; ?> modules completed
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="learning-actions-card">
                        <h6 class="mb-3">Academic Dashboard</h6>
                        <a href="profile" class="btn btn-outline-primary btn-sm mb-2 w-100">
                            <i class="fas fa-user-graduate me-1"></i>View My Profile
                        </a>
                        <a href="courses" class="btn btn-outline-success btn-sm mb-2 w-100">
                            <i class="fas fa-list me-1"></i>Explore All Programs
                        </a>
                        <?php if ($inProgressLessons > 0): ?>
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

    <!-- Featured Posts Section -->
<section id="featured-posts" class="featured-section py-5">
        <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title-modern">
                <i class="fas fa-star me-3 text-warning"></i>Featured Learning Materials
            </h2>
            <p class="section-subtitle-modern">Curated educational content to enhance your academic journey</p>
            </div>
            
        <div class="featured-posts-grid">
                <?php foreach ($featuredPosts as $post): ?>
            <div class="featured-post-card">
                <div class="card-header-modern">
                    <div class="badges-container">
                        <span class="category-badge-modern">
                            <i class="fas fa-folder me-1"></i>
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </span>
                        
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
                        <span class="premium-badge-modern">
                            <i class="fas fa-crown me-1"></i>Premium
                            </span>
                        <?php endif; ?>
                        <span class="featured-badge-modern">
                            <i class="fas fa-star me-1"></i>Featured
                        </span>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-outline-secondary bookmark-btn" 
                                data-post-id="<?php echo $post['id']; ?>" 
                                title="Bookmark">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
                        
                <div class="card-body-modern">
                    <h3 class="card-title-modern">
                                <a href="post.php?slug=<?php echo $post['slug']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                    </h3>
                            
                    <p class="card-excerpt">
                                <?php echo htmlspecialchars($post['excerpt']); ?>
                            </p>
                            
                            <?php if ($post['tags']): ?>
                    <div class="tags-container-modern">
                                <?php 
                                $tags = explode(',', $post['tags']);
                        foreach (array_slice($tags, 0, 4) as $tagItem): 
                                    $tagName = trim($tagItem);
                                    if (!empty($tagName)):
                                        $tagData = $tag->getTagByName($tagName);
                                        $tagColor = $tagData ? $tagData['color'] : '#6c757d';
                                        $tagSlug = $tagData ? $tagData['slug'] : strtolower(str_replace(' ', '-', $tagName));
                                ?>
                        <a href="tags.php?tag=<?php echo urlencode($tagSlug); ?>" class="tag-badge-modern" style="background-color: <?php echo htmlspecialchars($tagColor); ?>;">
                                    <?php echo htmlspecialchars($tagName); ?>
                                </a>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                        <?php if (count($tags) > 4): ?>
                        <span class="tag-badge-modern more-tags">+<?php echo count($tags) - 4; ?> more</span>
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
                                        Part of <?php echo htmlspecialchars($post['course_context']['title']); ?> course
                                    </small>
                                    <?php if ($isLoggedIn): ?>
                                        <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-play me-1"></i>Start Learning
                                        </a>
                                    <?php else: ?>
                                        <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                            <i class="fas fa-eye me-1"></i>View Tutorial
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                </div>
                
                <div class="card-footer-modern">
                    <div class="meta-info">
                        <div class="author-info">
                            <div class="author-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="author-details">
                                <span class="author-name"><?php echo htmlspecialchars($post['author_name']); ?></span>
                                <span class="publish-date"><?php echo date('M j, Y', strtotime($post['published_at'])); ?></span>
                            </div>
                        </div>
                            
                            <!-- Voting Section -->
                        <div class="voting-section-modern">
                            <?php if ($isLoggedIn): ?>
                            <?php 
                            $currentUserVote = $vote->getUserVote($post['id'], $currentUser['id']);
                            $userVoteType = $currentUserVote ? $currentUserVote['vote_type'] : '';
                            ?>
                            <div class="vote-buttons-modern">
                                <button class="vote-btn-modern upvote <?php echo $userVoteType === 'upvote' ? 'voted' : ''; ?>" 
                                                data-post-id="<?php echo $post['id']; ?>" 
                                                data-vote-type="upvote" 
                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                title="Upvote">
                                            <i class="fas fa-thumbs-up"></i>
                                    <span class="vote-count"><?php echo $post['upvotes'] ?? 0; ?></span>
                                        </button>
                                <button class="vote-btn-modern downvote <?php echo $userVoteType === 'downvote' ? 'voted' : ''; ?>" 
                                                data-post-id="<?php echo $post['id']; ?>" 
                                                data-vote-type="downvote" 
                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                title="Downvote">
                                            <i class="fas fa-thumbs-down"></i>
                                    <span class="vote-count"><?php echo $post['downvotes'] ?? 0; ?></span>
                                        </button>
                            </div>
                            <?php else: ?>
                            <div class="vote-stats-modern">
                                <span class="vote-stat">
                                    <i class="fas fa-thumbs-up text-success"></i>
                                    <?php echo $post['upvotes'] ?? 0; ?>
                                </span>
                                <span class="vote-stat">
                                    <i class="fas fa-thumbs-down text-danger"></i>
                                    <?php echo $post['downvotes'] ?? 0; ?>
                                </span>
                                    </div>
                            <?php endif; ?>
                            
                            <div class="vote-score-modern">
                                <span class="score-badge">
                                            <i class="fas fa-chart-line me-1"></i>
                                            <?php echo $post['vote_score'] ?? 0; ?>
                                        </span>
                                    </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
            
        <div class="text-center mt-5">
                            <a href="posts" class="btn btn-primary btn-lg view-all-btn">
                <i class="fas fa-arrow-right me-2"></i>Explore All Materials
            </a>
        </div>
        </div>
    </section>

    <!-- Trending Posts Section -->
    <?php if (!empty($trendingPosts)): ?>
<section class="trending-section py-5">
        <div class="container">
        <div class="section-header-modern mb-5">
            <div class="d-flex justify-content-between align-items-center">
                        <div>
                    <h2 class="section-title-modern">
                        <i class="fas fa-fire me-3 text-danger"></i>🔥 Popular Learning Materials
                    </h2>
                    <p class="section-subtitle-modern">Most accessed educational content this month</p>
                </div>
                <a href="posts?sort=trending" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-fire me-2"></i>View All Popular
                </a>
                </div>
            </div>
            
        <div class="trending-posts-grid">
                <?php foreach ($trendingPosts as $post): ?>
            <div class="trending-post-card">
                <div class="trending-indicator-modern">
                            <i class="fas fa-fire"></i>
                        </div>
                
                <div class="card-header-modern">
                    <div class="badges-container">
                        <span class="category-badge-modern">
                            <i class="fas fa-folder me-1"></i>
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </span>
                        
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
                        <span class="premium-badge-modern">
                            <i class="fas fa-crown me-1"></i>Premium
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-outline-secondary bookmark-btn" 
                                data-post-id="<?php echo $post['id']; ?>" 
                                title="Bookmark">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
                        
                <div class="card-body-modern">
                    <h3 class="card-title-modern">
                                <a href="post.php?slug=<?php echo $post['slug']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                    </h3>
                            
                    <p class="card-excerpt">
                                <?php echo htmlspecialchars($post['excerpt']); ?>
                            </p>
                            
                    <?php if ($post['tags']): ?>
                    <div class="tags-container-modern">
                        <?php 
                        $tags = explode(',', $post['tags']);
                        foreach (array_slice($tags, 0, 3) as $tagItem): 
                            $tagName = trim($tagItem);
                            if (!empty($tagName)):
                                $tagData = $tag->getTagByName($tagName);
                                $tagColor = $tagData ? $tagData['color'] : '#6c757d';
                                $tagSlug = $tagData ? $tagData['slug'] : strtolower(str_replace(' ', '-', $tagName));
                        ?>
                        <a href="tags.php?tag=<?php echo urlencode($tagSlug); ?>" class="tag-badge-modern" style="background-color: <?php echo htmlspecialchars($tagColor); ?>;">
                            <?php echo htmlspecialchars($tagName); ?>
                        </a>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                        <?php if (count($tags) > 3): ?>
                        <span class="tag-badge-modern more-tags">+<?php echo count($tags) - 3; ?> more</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Learning Progress Section -->
                    <?php if (isset($post['course_context']) && isset($post['user_progress'])): ?>
                    <div class="learning-progress-section mt-3">
                        <div class="progress-header">
                            <small class="text-muted">
                                <i class="fas fa-chart-line me-1"></i>
                                Your Progress
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
                                Part of <?php echo htmlspecialchars($post['course_context']['title']); ?> course
                            </small>
                            <?php if ($isLoggedIn): ?>
                                <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-play me-1"></i>Start Learning
                                </a>
                            <?php else: ?>
                                <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="fas fa-eye me-1"></i>View Tutorial
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                                </div>
                
                <div class="card-footer-modern">
                    <div class="meta-info">
                        <div class="author-info">
                            <div class="author-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="author-details">
                                <span class="author-name"><?php echo htmlspecialchars($post['author_name']); ?></span>
                                <span class="publish-date"><?php echo date('M j', strtotime($post['published_at'])); ?></span>
                                </div>
                            </div>
                            
                            <!-- Voting Section for Trending Posts -->
                        <div class="voting-section-modern">
                            <?php if ($isLoggedIn): ?>
                            <?php 
                            $currentUserVote = $vote->getUserVote($post['id'], $currentUser['id']);
                            $userVoteType = $currentUserVote ? $currentUserVote['vote_type'] : '';
                            ?>
                            <div class="vote-buttons-modern">
                                <button class="vote-btn-modern upvote <?php echo $userVoteType === 'upvote' ? 'voted' : ''; ?>" 
                                                data-post-id="<?php echo $post['id']; ?>" 
                                                data-vote-type="upvote" 
                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                title="Upvote">
                                            <i class="fas fa-thumbs-up"></i>
                                    <span class="vote-count"><?php echo $post['upvotes'] ?? 0; ?></span>
                                        </button>
                                        <button class="btn btn-sm <?php echo $userVoteType === 'downvote' ? 'btn-danger' : 'btn-outline-danger'; ?> vote-btn" 
                                                data-post-id="<?php echo $post['id']; ?>" 
                                                data-vote-type="downvote" 
                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                title="Downvote">
                                            <i class="fas fa-thumbs-down"></i>
                                            <span class="vote-count ms-1"><?php echo $post['downvotes'] ?? 0; ?></span>
                                        </button>
                            </div>
                            <?php else: ?>
                            <div class="vote-stats-modern">
                                <span class="vote-stat">
                                    <i class="fas fa-thumbs-up text-success"></i>
                                    <?php echo $post['upvotes'] ?? 0; ?>
                                </span>
                                <span class="vote-stat">
                                    <i class="fas fa-thumbs-down text-danger"></i>
                                    <?php echo $post['downvotes'] ?? 0; ?>
                                        </span>
                            </div>
    <?php endif; ?>

                            <div class="vote-score-modern">
                                <span class="score-badge">
                                    <i class="fas fa-chart-line me-1"></i>
                                    <?php echo $post['vote_score'] ?? 0; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

    <!-- Recommended Next Steps Section -->
    <?php if ($isLoggedIn && !empty($coursesWithProgress)): ?>
    <section class="recommended-next-steps py-5 bg-gradient-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title-modern">
                    <i class="fas fa-lightbulb me-3 text-warning"></i>Academic Recommendations
                </h2>
                <p class="section-subtitle-modern">Personalized suggestions based on your academic progress</p>
            </div>
            
            <div class="recommendations-grid">
                <?php
                $nextLessons = [];
                foreach ($coursesWithProgress as $courseItem) {
                    $userProgress = $course->getUserCourseProgress($currentUser['id'], $courseItem['id']);
                    if (!empty($userProgress)) {
                        // Find the next incomplete lesson
                        foreach ($userProgress as $lesson) {
                            if ($lesson['progress_percentage'] < 100) {
                                $nextLessons[] = [
                                    'course' => $courseItem,
                                    'lesson' => $lesson,
                                    'progress' => $lesson['progress_percentage']
                                ];
                                break; // Only get one lesson per course
                            }
                        }
                    }
                }
                
                // Limit to 4 recommendations
                $nextLessons = array_slice($nextLessons, 0, 4);
                ?>
                
                <?php if (!empty($nextLessons)): ?>
                    <div class="row">
                        <?php foreach ($nextLessons as $nextLesson): ?>
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="recommendation-card">
                                    <div class="card-header">
                                        <div class="course-badge">
                                            <i class="fas fa-graduation-cap me-1"></i>
                                            <?php echo htmlspecialchars($nextLesson['course']['title']); ?>
                                        </div>
                                        <div class="progress-badge">
                                            <?php echo $nextLesson['progress']; ?>% Complete
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="lesson-title">
                                            <?php echo htmlspecialchars($nextLesson['lesson']['title'] ?? 'Continue Learning'); ?>
                                        </h6>
                                        <div class="lesson-progress">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-warning" 
                                                     style="width: <?php echo $nextLesson['progress']; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                        <a href="post.php?slug=<?php echo $nextLesson['lesson']['slug'] ?? '#'; ?>" 
                                           class="btn btn-primary btn-sm w-100 mt-3">
                                            <i class="fas fa-play me-1"></i>Continue
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <div class="completion-celebration">
                            <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                            <h5 class="text-success">Academic Excellence!</h5>
                            <p class="text-muted">You've completed all your current academic modules.</p>
                            <a href="courses" class="btn btn-warning">
                                <i class="fas fa-plus me-1"></i>Explore New Programs
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categories Section -->
<section class="categories-section py-5">
        <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title-modern">
                <i class="fas fa-th-large me-3 text-primary"></i>Academic Disciplines
            </h2>
            <p class="section-subtitle-modern">Explore learning materials by subject area and academic level</p>
            </div>
            
        <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
            <div class="category-card-modern">
                <div class="category-icon-modern">
                    <i class="fas fa-code"></i>
                            </div>
                <div class="category-content">
                    <h3 class="category-title"><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <p class="category-description"><?php echo htmlspecialchars($cat['description']); ?></p>
                    <div class="category-meta">
                        <span class="tutorial-count"><?php echo $cat['post_count']; ?> tutorials</span>
                        <a href="categories?category=<?php echo $cat['slug']; ?>" class="explore-btn">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
<section class="newsletter-section-modern py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                <div class="newsletter-card">
                    <div class="newsletter-icon mb-4">
                        <i class="fas fa-envelope-open-text fa-3x text-primary"></i>
                    </div>
                    <h3 class="mb-3">Stay Connected with Telie Academy</h3>
                    <p class="text-muted mb-4">Receive updates on new learning materials, academic insights, and exclusive educational content delivered to your inbox.</p>
                    
                    <div class="newsletter-form-modern">
                        <div class="input-group">
                            <input type="email" class="form-control form-control-lg" id="newsletterEmail" placeholder="Enter your email address">
                            <button class="btn btn-primary btn-lg" type="button" id="newsletterSubmit">
                                <i class="fas fa-paper-plane me-2"></i>Subscribe
                            </button>
                        </div>
                        <div id="newsletterMessage" class="alert mt-3" style="display: none;"></div>
                    </div>
                    
                    <div class="newsletter-benefits mt-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="benefit-item">
                                    <i class="fas fa-bell text-primary mb-2"></i>
                                    <small>Academic Updates</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="benefit-item">
                                    <i class="fas fa-gift text-primary mb-2"></i>
                                    <small>Premium Materials</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="benefit-item">
                                    <i class="fas fa-shield-alt text-primary mb-2"></i>
                                    <small>No Spam</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/modals.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    
    <style>
    /* Learning Paths Course Cards */
    .learning-paths-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .course-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
    }
    
    .course-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }
    
    .course-card-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .course-progress-ring {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border: 3px solid rgba(255, 255, 255, 0.3);
    }
    
    .course-progress-ring[data-progress]:not([data-progress="0"]) {
        background: conic-gradient(
            #28a745 0deg,
            #28a745 calc(var(--progress, 0) * 3.6deg),
            rgba(255, 255, 255, 0.2) calc(var(--progress, 0) * 3.6deg),
            rgba(255, 255, 255, 0.2) 360deg
        );
    }
    
    .progress-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: white;
    }
    
    .course-meta {
        text-align: right;
    }
    
    .course-modules,
    .course-lessons {
        display: block;
        font-size: 0.75rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }
    
    .course-card-body {
        padding: 1.5rem;
        flex-grow: 1;
    }
    
    .course-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    
    .course-description {
        color: #6c757d;
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }
    
    .enrollment-status {
        padding: 0.5rem 0.75rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #28a745;
    }
    
    .course-stats {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }
    
    .stat-item {
        font-size: 0.875rem;
        color: #6c757d;
        display: flex;
        align-items: center;
    }
    
    .course-card-footer {
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }
    
    .course-card-footer .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .course-card-footer .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .course-card-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .course-progress-ring {
            width: 50px;
            height: 50px;
        }
        
        .course-meta {
            text-align: center;
        }
        
        .course-card-body {
            padding: 1rem;
        }
        
        .course-stats {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }
    }
    
    /* Progress ring animation */
    .course-progress-ring[data-progress] {
        --progress: attr(data-progress number);
    }
    
    @keyframes progressRing {
        from {
            --progress: 0;
        }
        to {
            --progress: attr(data-progress number);
        }
    }
    
    .course-card:hover .course-progress-ring {
        animation: progressRing 1s ease-in-out;
    }
    
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
    
    /* Learning Progress Overview Section */
    .learning-progress-overview {
        border-top: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
    }
    
    .progress-stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .progress-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }
    
    .stat-icon {
        margin-bottom: 1rem;
    }
    
    .stat-icon i {
        font-size: 2rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .overall-progress-bar {
        background: #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        padding: 1rem;
    }
    
    .overall-progress-bar .progress {
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .overall-progress-bar .progress-bar {
        background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
        transition: width 0.6s ease;
    }
    
    .learning-actions-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    
    .learning-actions-card h6 {
        color: #2d3748;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
    }
    
    .learning-actions-card .btn {
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }
    
    .learning-actions-card .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Recommended Next Steps Section */
    .bg-gradient-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }
    
    .recommendation-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .recommendation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }
    
    .recommendation-card .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .recommendation-card .course-badge {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }
    
    .recommendation-card .progress-badge {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }
    
    .recommendation-card .lesson-title {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 1rem;
        line-height: 1.3;
    }
    
    .recommendation-card .lesson-progress .progress {
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    
    .recommendation-card .lesson-progress .progress-bar {
        background: #ffc107;
        transition: width 0.6s ease;
    }
    
    .completion-celebration {
        padding: 2rem;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 16px;
        border: 1px solid #fbbf24;
    }
    
    .completion-celebration h5 {
        color: #059669;
        margin-bottom: 1rem;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .course-badge-modern,
        .module-badge-modern {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .progress-stat-card {
            margin-bottom: 1rem;
        }
        
        .learning-actions-card {
            margin-top: 1rem;
        }
        
        .recommendation-card {
            margin-bottom: 1rem;
        }
        
        .recommendation-card .card-header {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }
    }
    </style>
    
    <script>
    // Initialize course progress rings
    function initializeCourseProgressRings() {
        const progressRings = document.querySelectorAll('.course-progress-ring[data-progress]');
        progressRings.forEach(ring => {
            const progress = parseFloat(ring.getAttribute('data-progress')) || 0;
            ring.style.setProperty('--progress', progress);
            
            // Animate progress ring on scroll into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        ring.style.background = progress > 0 ? 
                            `conic-gradient(#28a745 0deg, #28a745 ${progress * 3.6}deg, rgba(255,255,255,0.2) ${progress * 3.6}deg, rgba(255,255,255,0.2) 360deg)` :
                            'rgba(255, 255, 255, 0.2)';
                        observer.unobserve(ring);
                    }
                });
            }, { threshold: 0.5 });
            
            observer.observe(ring);
        });
    }
    
    // Initialize voting and bookmark functionality for listing pages
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing listing page functionality...');
        
        // Initialize course progress rings
        initializeCourseProgressRings();
        
        // Initialize voting functionality
        if (typeof initializeVoting === 'function') {
            console.log('Initializing voting functionality...');
            initializeVoting();
        } else {
            console.warn('initializeVoting function not found');
        }
        
        // Initialize bookmark functionality
        if (typeof initializeBookmarks === 'function') {
            console.log('Initializing bookmark functionality...');
            initializeBookmarks();
        } else {
            console.warn('initializeBookmarks function not found');
        }
        
        // Check if vote buttons exist (using the correct class names)
        const voteButtons = document.querySelectorAll('.vote-btn-modern');
        console.log('Vote buttons found on listing page:', voteButtons.length);
        
        // Check if bookmark buttons exist
        const bookmarkButtons = document.querySelectorAll('.bookmark-btn');
        console.log('Bookmark buttons found on listing page:', bookmarkButtons.length);
        
        // If no vote buttons found with .vote-btn-modern, try .vote-btn
        if (voteButtons.length === 0) {
            const altVoteButtons = document.querySelectorAll('.vote-btn');
            console.log('Alternative vote buttons (.vote-btn) found:', altVoteButtons.length);
        }
    });
    

    </script> 