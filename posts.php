<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/User.php';
require_once 'includes/Vote.php';
require_once 'includes/Tag.php';
require_once 'includes/Course.php';

$post = new Post();
$user = new User();
$vote = new Vote();
$tag = new Tag();
$course = new Course();

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

// Get sort parameter
$sortBy = $_GET['sort'] ?? 'date';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12; // Increased for better grid layout
$offset = ($page - 1) * $perPage;

// Get course filter if specified
$courseFilter = $_GET['course'] ?? null;
$moduleFilter = $_GET['module'] ?? null;

// Get posts with course context
$posts = $post->getAllPosts($perPage, $offset, $isPremium, $sortBy);
$totalPosts = $post->getPostCount($isPremium);
$totalPages = ceil($totalPosts / $perPage);

// Enhance posts with course information
if ($isLoggedIn && !empty($posts)) {
    $currentUser = $user->getCurrentUser();
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

// Get available courses for filtering
$availableCourses = $course->getAllCourses();

// Set page variables for head component
$pageTitle = 'Learning Materials';
$pageDescription = 'Explore comprehensive learning materials, educational content, and academic resources. Complete collection of educational materials.';

include 'includes/head.php';
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="posts-hero py-5">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-4 text-gradient">
                    <i class="fas fa-graduation-cap me-3"></i>Learning Materials
                </h1>
                <p class="lead text-muted mb-4">Discover comprehensive educational content, academic guides, and learning resources</p>
                
                <!-- Search Bar -->
                <div class="search-container mb-4">
                    <form action="search.php" method="GET" class="search-form-large">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" name="q" placeholder="Search learning materials..." aria-label="Search learning materials">
                            <button class="btn btn-primary btn-lg" type="submit">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Stats -->
                <div class="stats-container">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $totalPosts; ?></div>
                                <div class="stat-label">Total Learning Materials</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $isPremium ? 'Premium' : 'Free'; ?></div>
                                <div class="stat-label">Access Level</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $page; ?>/<?php echo $totalPages; ?></div>
                                <div class="stat-label">Current Page</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sorting and Filtering Section -->
<section class="sorting-section py-4 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 text-muted">
                    <i class="fas fa-sort me-2"></i>Organize by:
                </h5>
            </div>
            <div class="col-md-6">
                <div class="sorting-controls">
                    <div class="btn-group w-100" role="group" aria-label="Organize learning materials">
                            <a href="posts.php?sort=date<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                           class="btn <?php echo $sortBy === 'date' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-clock me-1"></i>Latest
                            </a>
                            <a href="posts.php?sort=votes<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                           class="btn <?php echo $sortBy === 'votes' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-thumbs-up me-1"></i>Most Voted
                            </a>
                            <a href="posts.php?sort=upvotes<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                           class="btn <?php echo $sortBy === 'upvotes' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-arrow-up me-1"></i>Most Upvoted
                            </a>
                            <a href="posts.php?sort=trending<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                           class="btn <?php echo $sortBy === 'trending' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-fire me-1"></i>Trending
                            </a>
                            <a href="posts.php?sort=course<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                           class="btn <?php echo $sortBy === 'course' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-graduation-cap me-1"></i>By Program
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Course Filtering Section -->
    <?php if (!empty($availableCourses)): ?>
    <section class="course-filtering-section py-3 bg-primary bg-opacity-10">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-graduation-cap me-2"></i>Filter by Academic Program:
                    </h6>
                </div>
                <div class="col-md-9">
                    <div class="course-filters">
                        <a href="posts.php<?php echo isset($_GET['sort']) ? '?sort=' . $_GET['sort'] : ''; ?>" 
                           class="btn <?php echo !$courseFilter ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm me-2">
                            <i class="fas fa-globe me-1"></i>All Programs
                        </a>
                        <?php foreach ($availableCourses as $courseItem): ?>
                            <a href="posts.php?course=<?php echo $courseItem['slug']; ?><?php echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : ''; ?>" 
                               class="btn <?php echo $courseFilter === $courseItem['slug'] ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm me-2">
                                <i class="fas fa-book me-1"></i><?php echo htmlspecialchars($courseItem['title']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<!-- Learning Insights Section -->
<?php if ($isLoggedIn && !empty($availableCourses)): ?>
<section class="learning-insights-section py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                                    <h5 class="mb-3">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Your Academic Journey
                    </h5>
                <div class="learning-stats">
                    <?php
                    $totalCoursePosts = 0;
                    $completedCoursePosts = 0;
                    $overallProgress = 0;
                    
                    foreach ($availableCourses as $courseItem) {
                        $userProgress = $course->getUserCourseProgress($user->getCurrentUser()['id'], $courseItem['id']);
                        $coursePosts = count($userProgress);
                        $courseCompleted = count(array_filter($userProgress, function($p) { return $p['progress_percentage'] == 100; }));
                        
                        $totalCoursePosts += $coursePosts;
                        $completedCoursePosts += $courseCompleted;
                    }
                    
                    if ($totalCoursePosts > 0) {
                        $overallProgress = round(($completedCoursePosts / $totalCoursePosts) * 100);
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="insight-card">
                                <div class="insight-icon">
                                    <i class="fas fa-book-open text-primary"></i>
                                </div>
                                <div class="insight-content">
                                    <div class="insight-number"><?php echo $totalCoursePosts; ?></div>
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
                                    <div class="insight-number"><?php echo $completedCoursePosts; ?></div>
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
                                    <div class="insight-number"><?php echo $overallProgress; ?>%</div>
                                                                            <div class="insight-label">Academic Achievement</div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <?php if ($overallProgress > 0): ?>
                                                    <a href="profile" class="btn btn-outline-warning btn-sm w-100">
                                <i class="fas fa-trophy me-1"></i>View Academic Achievements
                            </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Posts Grid Section -->
<section class="posts-section py-5">
        <div class="container">
                <?php if (empty($posts)): ?>
        <div class="no-posts-container text-center py-5">
            <div class="no-posts-icon mb-4">
                <i class="fas fa-book-open fa-4x text-muted"></i>
            </div>
            <h3 class="text-muted mb-3">No learning materials found</h3>
            <p class="text-muted mb-4">No educational content available yet. Check back soon for new materials!</p>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Go to Homepage
            </a>
                </div>
                <?php else: ?>
        
        <!-- Posts Grid -->
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
            <div class="post-card-modern">
                <!-- Card Header with Badges -->
                <div class="card-header-modern">
                    <div class="badges-container">
                        <span class="category-badge-modern">
                            <i class="fas fa-folder me-1"></i>
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </span>
                        
                        <!-- Course Badge -->
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
                        
                <!-- Card Body -->
                <div class="card-body-modern">
                    <h3 class="card-title-modern">
                                <a href="post.php?slug=<?php echo $post['slug']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                    </h3>
                            
                    <p class="card-excerpt">
                                <?php echo htmlspecialchars($post['excerpt']); ?>
                            </p>
                            
                    <!-- Tags -->
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
                
                <!-- Card Footer -->
                <div class="card-footer-modern">
                    <div class="meta-info">
                        <div class="author-info">
                            <div class="author-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="author-details">
                                <span class="author-name"><?php echo htmlspecialchars($post['author_name']); ?></span>
                                <span class="publish-date"><?php echo date('M j, Y', strtotime($post['published_at'])); ?></span>
                                <span class="view-count">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo number_format($post['view_count'] ?? 0); ?>
                                </span>
                            </div>
                        </div>
                            
                            <!-- Voting Section -->
                        <div class="voting-section-modern">
                            <?php if ($isLoggedIn): ?>
                            <?php 
                            $currentUserVote = $vote->getUserVote($post['id'], $user->getCurrentUser()['id']);
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

        <!-- Enhanced Pagination -->
            <?php if ($totalPages > 1): ?>
        <div class="pagination-container mt-5">
                    <nav aria-label="Posts pagination">
                <ul class="pagination-modern justify-content-center">
                    <!-- First Page -->
                    <?php if ($page > 1): ?>
                    <li class="page-item-modern">
                        <a class="page-link-modern" href="posts.php?page=1&sort=<?php echo $sortBy; ?>" title="First Page">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Previous Page -->
                            <?php if ($page > 1): ?>
                    <li class="page-item-modern">
                        <a class="page-link-modern" href="posts.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sortBy; ?>">
                            <i class="fas fa-angle-left me-1"></i>Previous
                        </a>
                            </li>
                            <?php endif; ?>
                            
                    <!-- Page Numbers -->
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item-modern <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link-modern" href="posts.php?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                    <!-- Next Page -->
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item-modern">
                        <a class="page-link-modern" href="posts.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sortBy; ?>">
                            Next<i class="fas fa-angle-right ms-1"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Last Page -->
                            <?php if ($page < $totalPages): ?>
                    <li class="page-item-modern">
                        <a class="page-link-modern" href="posts.php?page=<?php echo $totalPages; ?>&sort=<?php echo $sortBy; ?>" title="Last Page">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
            
            <!-- Page Info -->
            <div class="page-info text-center mt-3">
                <small class="text-muted">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?> 
                    (<?php echo $totalPosts; ?> total learning materials)
                </small>
            </div>
        </div>
        <?php endif; ?>
        
            <?php endif; ?>
        </div>
    </section>

<!-- Continue Learning Section -->
<?php if ($isLoggedIn && !empty($availableCourses)): ?>
<section class="continue-learning-section py-5 bg-gradient-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h4 class="text-white mb-3">
                    <i class="fas fa-play-circle me-2"></i>
                    Continue Your Academic Journey
                </h4>
                <p class="text-white-50 mb-4">Pick up where you left off and keep progressing through your academic programs</p>
                
                <div class="next-lessons-grid">
                    <?php
                    $nextLessons = [];
                    foreach ($availableCourses as $courseItem) {
                        $userProgress = $course->getUserCourseProgress($user->getCurrentUser()['id'], $courseItem['id']);
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
                    
                    // Limit to 3 next lessons
                    $nextLessons = array_slice($nextLessons, 0, 3);
                    ?>
                    
                    <?php if (!empty($nextLessons)): ?>
                        <div class="row">
                            <?php foreach ($nextLessons as $nextLesson): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="next-lesson-card">
                                        <div class="lesson-header">
                                            <div class="course-badge">
                                                <i class="fas fa-graduation-cap me-1"></i>
                                                <?php echo htmlspecialchars($nextLesson['course']['title']); ?>
                                            </div>
                                            <div class="progress-badge">
                                                <?php echo $nextLesson['progress']; ?>% Complete
                                            </div>
                                        </div>
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
                                           class="btn btn-light btn-sm w-100 mt-2">
                                            <i class="fas fa-play me-1"></i>Continue
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <p class="text-white-50 mb-3">Great job! You've completed all your current academic modules.</p>
                            <a href="courses" class="btn btn-light">
                                <i class="fas fa-plus me-1"></i>Explore New Programs
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="motivation-card text-center">
                    <div class="motivation-icon mb-3">
                        <i class="fas fa-trophy fa-3x text-warning"></i>
                    </div>
                    <h5 class="text-white mb-2">Keep Learning!</h5>
                    <p class="text-white-50 mb-3">Every lesson completed brings you closer to mastering new skills.</p>
                                            <div class="motivation-stats">
                            <div class="stat-item">
                                <span class="stat-number text-warning"><?php echo $completedCoursePosts; ?></span>
                                <span class="stat-label text-white-50">Modules Completed</span>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Enhanced Newsletter Section -->
<section class="newsletter-section-modern py-5">
        <div class="container">
            <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="newsletter-card">
                    <div class="newsletter-icon mb-4">
                        <i class="fas fa-envelope-open-text fa-3x text-primary"></i>
                    </div>
                    <h3 class="mb-3">Stay Connected with Telie Academy</h3>
                    <p class="text-muted mb-4">Get the latest learning materials, academic insights, and educational content delivered directly to your inbox.</p>
                    
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
    
    <script>
    // Initialize voting and bookmark functionality for listing pages
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing posts listing page functionality...');
        
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
        console.log('Vote buttons found on posts listing page:', voteButtons.length);
        
        // Check if bookmark buttons exist
        const bookmarkButtons = document.querySelectorAll('.bookmark-btn');
        console.log('Bookmark buttons found on posts listing page:', bookmarkButtons.length);
        
        // If no vote buttons found with .vote-btn-modern, try .vote-btn
        if (voteButtons.length === 0) {
            const altVoteButtons = document.querySelectorAll('.vote-btn');
            console.log('Alternative vote buttons (.vote-btn) found:', altVoteButtons.length);
        }
    });
    </script>
    
    <style>
    /* Course Filtering Styles */
    .course-filtering-section {
        border-top: 1px solid rgba(13, 110, 253, 0.2);
        border-bottom: 1px solid rgba(13, 110, 253, 0.2);
    }
    
    .course-filters .btn {
        border-radius: 20px;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }
    
    .course-filters .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
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
        .course-filters {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .course-filters .btn {
            margin-bottom: 0.5rem;
        }
        
        .insight-card {
            margin-bottom: 1rem;
        }
        
        .learning-actions {
            margin-top: 1rem;
        }
        
        .course-badge-modern,
        .module-badge-modern {
            display: block;
            margin-bottom: 0.5rem;
        }
    }
    
    /* Continue Learning Section */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    .next-lesson-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .next-lesson-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }
    
    .lesson-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .course-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }
    
    .progress-badge {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }
    
    .lesson-title {
        color: white;
        font-weight: 600;
        margin-bottom: 1rem;
        line-height: 1.3;
    }
    
    .lesson-progress .progress {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    
    .lesson-progress .progress-bar {
        background: #ffc107;
        transition: width 0.6s ease;
    }
    
    .motivation-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        height: 100%;
    }
    
    .motivation-icon i {
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }
    
    .motivation-stats .stat-item {
        text-align: center;
    }
    
    .motivation-stats .stat-number {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .motivation-stats .stat-label {
        font-size: 0.875rem;
        opacity: 0.8;
    }
    
    /* Responsive adjustments for continue learning */
    @media (max-width: 768px) {
        .next-lesson-card {
            margin-bottom: 1rem;
        }
        
        .motivation-card {
            margin-top: 1rem;
        }
        
        .lesson-header {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }
    }
    </style> 