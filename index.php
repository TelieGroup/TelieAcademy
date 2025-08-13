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
$currentUser = $isLoggedIn ? $user->getCurrentUser() : null;
$isPremium = $currentUser && isset($currentUser['is_premium']) && $currentUser['is_premium'] ? true : false;

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
<section class="hero-section-modern text-center text-white">
        <div class="container">
            <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="hero-content-modern">
                    <div class="hero-icon-modern mb-4">
                        <i class="fas fa-rocket"></i>
                        </div>
                    <h1 class="hero-title-modern mb-4">
                        <span class="text-gradient">Learn Modern Web Development</span>
                    </h1>
                    <p class="hero-subtitle-modern mb-5">
                        Master JavaScript, React, Python, and more with our comprehensive tutorials designed for developers and students.
                    </p>
                    
                    <div class="hero-actions-modern mb-5">
                        <a href="#featured-posts" class="btn btn-primary btn-lg me-3 hero-btn-primary">
                                <i class="fas fa-play me-2"></i>Start Learning
                            </a>
                        <a href="categories.php" class="btn btn-outline-light btn-lg hero-btn-secondary">
                                <i class="fas fa-folder me-2"></i>Browse Categories
                            </a>
                        </div>
                        
                    <!-- Search Bar -->
                    <div class="hero-search-container mb-5">
                        <form action="search.php" method="GET" class="hero-search-form">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" name="q" placeholder="Search tutorials..." aria-label="Search tutorials">
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
                                    <div class="stat-label-modern">Tutorials</div>
                                </div>
                                    </div>
                            <div class="col-md-3">
                                <div class="stat-item-modern">
                                    <div class="stat-number-modern"><?php echo count($categories); ?></div>
                                    <div class="stat-label-modern">Categories</div>
                                </div>
                                    </div>
                            <div class="col-md-3">
                                <div class="stat-item-modern">
                                    <div class="stat-number-modern"><?php echo count($popularTags); ?></div>
                                    <div class="stat-label-modern">Topics</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item-modern">
                                    <div class="stat-number-modern"><?php echo $isPremium ? 'Premium' : 'Free'; ?></div>
                                    <div class="stat-label-modern">Access</div>
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
<section id="featured-posts" class="featured-section py-5">
        <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title-modern">
                <i class="fas fa-star me-3 text-warning"></i>Featured Tutorials
            </h2>
            <p class="section-subtitle-modern">Hand-picked tutorials to get you started</p>
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
            <a href="posts.php" class="btn btn-primary btn-lg view-all-btn">
                <i class="fas fa-arrow-right me-2"></i>View All Tutorials
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
                        <i class="fas fa-fire me-3 text-danger"></i>🔥 Trending Tutorials
                    </h2>
                    <p class="section-subtitle-modern">Most popular tutorials this month</p>
                </div>
                <a href="posts.php?sort=trending" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-fire me-2"></i>View All Trending
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

    <!-- Categories Section -->
<section class="categories-section py-5">
        <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title-modern">
                <i class="fas fa-th-large me-3 text-primary"></i>Explore Categories
            </h2>
            <p class="section-subtitle-modern">Find tutorials by topic and skill level</p>
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
                        <a href="categories.php?category=<?php echo $cat['slug']; ?>" class="explore-btn">
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
                    <h3 class="mb-3">Stay Updated with New Tutorials</h3>
                    <p class="text-muted mb-4">Get the latest tutorials, coding tips, and tech insights delivered directly to your inbox.</p>
                    
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
                                    <small>Weekly Updates</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="benefit-item">
                                    <i class="fas fa-gift text-primary mb-2"></i>
                                    <small>Exclusive Content</small>
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
        console.log('Initializing listing page functionality...');
        
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