<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/User.php';
require_once 'includes/Vote.php';
require_once 'includes/Tag.php';

$post = new Post();
$user = new User();
$vote = new Vote();
$tag = new Tag();

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

// Get sort parameter
$sortBy = $_GET['sort'] ?? 'date';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12; // Increased for better grid layout
$offset = ($page - 1) * $perPage;

$posts = $post->getAllPosts($perPage, $offset, $isPremium, $sortBy);
$totalPosts = $post->getPostCount($isPremium);
$totalPages = ceil($totalPosts / $perPage);

// Set page variables for head component
$pageTitle = 'All Tutorials';
$pageDescription = 'Browse all available tutorials and learning resources. Comprehensive collection of tech tutorials.';

include 'includes/head.php';
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="posts-hero py-5 mt-5">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-4 text-gradient">
                    <i class="fas fa-graduation-cap me-3"></i>All Tutorials
                </h1>
                <p class="lead text-muted mb-4">Discover comprehensive tech tutorials, coding guides, and learning resources</p>
                
                <!-- Search Bar -->
                <div class="search-container mb-4">
                    <form action="search.php" method="GET" class="search-form-large">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" name="q" placeholder="Search tutorials..." aria-label="Search tutorials">
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
                                <div class="stat-label">Total Tutorials</div>
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
                    <i class="fas fa-sort me-2"></i>Sort by:
                </h5>
            </div>
            <div class="col-md-6">
                <div class="sorting-controls">
                    <div class="btn-group w-100" role="group" aria-label="Sort tutorials">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Posts Grid Section -->
<section class="posts-section py-5">
        <div class="container">
                <?php if (empty($posts)): ?>
        <div class="no-posts-container text-center py-5">
            <div class="no-posts-icon mb-4">
                <i class="fas fa-book-open fa-4x text-muted"></i>
            </div>
            <h3 class="text-muted mb-3">No tutorials found</h3>
            <p class="text-muted mb-4">No tutorials available yet. Check back soon for new content!</p>
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
                    (<?php echo $totalPosts; ?> total tutorials)
                </small>
            </div>
        </div>
        <?php endif; ?>
        
            <?php endif; ?>
        </div>
    </section>

<!-- Enhanced Newsletter Section -->
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