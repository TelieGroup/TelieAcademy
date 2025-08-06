<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/Category.php';
require_once 'includes/Tag.php';
require_once 'includes/User.php';
require_once 'includes/Vote.php';

// Initialize classes
$post = new Post();
$category = new Category();
$tag = new Tag();
$user = new User();
$vote = new Vote();

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

// Get sort parameter
$sortBy = $_GET['sort'] ?? 'date';

// Get featured posts
$featuredPosts = $post->getFeaturedPosts(3, $isPremium);

// Get trending posts (posts with high vote scores in recent time)
$trendingPosts = $vote->getTrendingPosts(6, $isPremium);

// Get all categories
$categories = $category->getAllCategories();

// Get popular tags
$popularTags = $tag->getPopularTags(10);

// Get total post count
$totalPosts = $post->getPostCount($isPremium);

// Set page variables for head component
$pageTitle = 'Home';
$pageDescription = 'Learn modern web development, JavaScript, React, Python and more with our comprehensive tutorials.';

include 'includes/head.php';
?>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section text-center text-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <div class="hero-icon mb-3">
                            <i class="fas fa-code"></i>
                        </div>
                        <h1 class="hero-title mb-3">Learn Modern Web Development</h1>
                        <p class="hero-subtitle mb-4">Master JavaScript, React, Python, and more with our comprehensive tutorials designed for developers and students.</p>
                        
                        <div class="hero-actions mb-4">
                            <a href="#featured-posts" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-play me-2"></i>Start Learning
                            </a>
                            <a href="categories.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-folder me-2"></i>Browse Categories
                            </a>
                        </div>
                        
                        <div class="hero-stats">
                            <div class="row justify-content-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo $totalPosts; ?>+</div>
                                        <div class="stat-label">Tutorials</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo count($categories); ?></div>
                                        <div class="stat-label">Categories</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo count($popularTags); ?></div>
                                        <div class="stat-label">Topics</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Posts Section -->
    <section id="featured-posts" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-5">Featured Tutorials</h2>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($featuredPosts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                <?php if ($post['is_premium']): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-crown me-1"></i>Premium
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="card-title">
                                <a href="post.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none">
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
                                <a href="tags.php?tag=<?php echo urlencode($tagSlug); ?>" class="badge text-decoration-none me-1" style="background-color: <?php echo htmlspecialchars($tagColor); ?>; color: white;">
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
                            
                            <!-- Voting Section -->
                            <?php if ($isLoggedIn): ?>
                            <?php 
                            // Get user's current vote once to avoid multiple database calls
                            $currentUserVote = $vote->getUserVote($post['id'], $user->getCurrentUser()['id']);
                            $userVoteType = $currentUserVote ? $currentUserVote['vote_type'] : '';
                            ?>
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="vote-buttons d-flex align-items-center">
                                        <button class="btn btn-sm <?php echo $userVoteType === 'upvote' ? 'btn-success' : 'btn-outline-success'; ?> vote-btn" 
                                                data-post-id="<?php echo $post['id']; ?>" 
                                                data-vote-type="upvote" 
                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                title="Upvote">
                                            <i class="fas fa-thumbs-up"></i>
                                            <span class="vote-count ms-1"><?php echo $post['upvotes'] ?? 0; ?></span>
                                        </button>
                                        <button class="btn btn-sm <?php echo $userVoteType === 'downvote' ? 'btn-danger' : 'btn-outline-danger'; ?> vote-btn ms-2" 
                                                data-post-id="<?php echo $post['id']; ?>" 
                                                data-vote-type="downvote" 
                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                title="Downvote">
                                            <i class="fas fa-thumbs-down"></i>
                                            <span class="vote-count ms-1"><?php echo $post['downvotes'] ?? 0; ?></span>
                                        </button>
                                    </div>
                                    <div class="vote-score">
                                        <span class="badge bg-primary">
                                            <i class="fas fa-chart-line me-1"></i>
                                            <?php echo $post['vote_score'] ?? 0; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="vote-stats">
                                        <small class="text-muted">
                                            <i class="fas fa-thumbs-up text-success me-1"></i><?php echo $post['upvotes'] ?? 0; ?>
                                            <i class="fas fa-thumbs-down text-danger ms-2 me-1"></i><?php echo $post['downvotes'] ?? 0; ?>
                                        </small>
                                    </div>
                                    <div class="vote-score">
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-chart-line me-1"></i>
                                            <?php echo $post['vote_score'] ?? 0; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="posts.php" class="btn btn-primary">View All Tutorials</a>
            </div>
        </div>
    </section>

    <!-- Trending Posts Section -->
    <?php if (!empty($trendingPosts)): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h2 class="mb-1">🔥 Trending Tutorials</h2>
                            <p class="text-muted mb-0">Most popular tutorials this month</p>
                        </div>
                        <a href="posts.php?sort=trending" class="btn btn-outline-primary">
                            <i class="fas fa-fire me-1"></i>View All Trending
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($trendingPosts as $post): ?>
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 shadow-sm trending-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                <div class="d-flex align-items-center">
                                    <?php if ($post['is_premium']): ?>
                                    <span class="badge bg-warning text-dark me-2">
                                        <i class="fas fa-crown me-1"></i>Premium
                                    </span>
                                    <?php endif; ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-chart-line me-1"></i><?php echo $post['vote_score'] ?? 0; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <h5 class="card-title">
                                <a href="post.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h5>
                            
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($post['excerpt']); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($post['author_name']); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i><?php echo date('M j', strtotime($post['published_at'])); ?>
                                </small>
                            </div>
                            
                            <!-- Voting Section for Trending Posts -->
                            <?php if ($isLoggedIn): ?>
                            <?php 
                            // Get user's current vote
                            $currentUserVote = $vote->getUserVote($post['id'], $user->getCurrentUser()['id']);
                            $userVoteType = $currentUserVote ? $currentUserVote['vote_type'] : '';
                            ?>
                            <div class="pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="vote-buttons d-flex align-items-center">
                                        <button class="btn btn-sm <?php echo $userVoteType === 'upvote' ? 'btn-success' : 'btn-outline-success'; ?> vote-btn" 
                                                data-post-id="<?php echo $post['id']; ?>" 
                                                data-vote-type="upvote" 
                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                title="Upvote">
                                            <i class="fas fa-thumbs-up"></i>
                                            <span class="vote-count ms-1"><?php echo $post['upvotes'] ?? 0; ?></span>
                                        </button>
                                        <button class="btn btn-sm <?php echo $userVoteType === 'downvote' ? 'btn-danger' : 'btn-outline-danger'; ?> vote-btn ms-2" 
                                                data-post-id="<?php echo $post['id']; ?>" 
                                                data-vote-type="downvote" 
                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                title="Downvote">
                                            <i class="fas fa-thumbs-down"></i>
                                            <span class="vote-count ms-1"><?php echo $post['downvotes'] ?? 0; ?></span>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-thumbs-up text-success me-1"></i><?php echo $post['upvotes'] ?? 0; ?>
                                        <i class="fas fa-thumbs-down text-danger ms-1 me-1"></i><?php echo $post['downvotes'] ?? 0; ?>
                                    </small>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-muted">
                                        <i class="fas fa-thumbs-up text-success me-1"></i><?php echo $post['upvotes'] ?? 0; ?>
                                        <i class="fas fa-thumbs-down text-danger ms-2 me-1"></i><?php echo $post['downvotes'] ?? 0; ?>
                                    </small>
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <i class="fas fa-sign-in-alt me-1"></i>Login to Vote
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Banner Ads Section -->
    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="ad-banner-container">
                        <div class="ad-banner" id="homepage-banner-ad">
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

    <!-- Categories Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-5">Explore Categories</h2>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($categories as $cat): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="category-icon mb-3">
                                <i class="fas fa-code fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($cat['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($cat['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary"><?php echo $cat['post_count']; ?> tutorials</span>
                                <a href="categories.php?category=<?php echo $cat['slug']; ?>" class="btn btn-outline-primary btn-sm">Explore</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Stay Updated</h2>
                    <p class="lead mb-4">Get the latest tutorials and tech insights delivered to your inbox.</p>
                    <div class="newsletter-form">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" id="newsletterEmail" placeholder="Enter your email address">
                            <button class="btn btn-primary" type="button" id="newsletterSubmit">
                                <i class="fas fa-paper-plane me-2"></i>Subscribe
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

    <?php include 'includes/scripts.php'; ?> 