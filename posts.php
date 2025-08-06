<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/User.php';
require_once 'includes/Vote.php';

$post = new Post();
$user = new User();
$vote = new Vote();

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

// Get sort parameter
$sortBy = $_GET['sort'] ?? 'date';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;
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

    <section class="py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-4">All Tutorials</h1>
                    <p class="lead text-muted">Browse all available tutorials and learning resources</p>
                    <p class="text-muted">Showing <?php echo count($posts); ?> of <?php echo $totalPosts; ?> tutorials</p>
                </div>
            </div>
            
            <!-- Sorting Controls -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        <div class="btn-group" role="group" aria-label="Sort tutorials">
                            <a href="posts.php?sort=date<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                               class="btn btn-outline-primary <?php echo $sortBy === 'date' ? 'active' : ''; ?>">
                                <i class="fas fa-clock me-1"></i>Latest
                            </a>
                            <a href="posts.php?sort=votes<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                               class="btn btn-outline-primary <?php echo $sortBy === 'votes' ? 'active' : ''; ?>">
                                <i class="fas fa-thumbs-up me-1"></i>Most Voted
                            </a>
                            <a href="posts.php?sort=upvotes<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                               class="btn btn-outline-primary <?php echo $sortBy === 'upvotes' ? 'active' : ''; ?>">
                                <i class="fas fa-arrow-up me-1"></i>Most Upvoted
                            </a>
                            <a href="posts.php?sort=trending<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                               class="btn btn-outline-primary <?php echo $sortBy === 'trending' ? 'active' : ''; ?>">
                                <i class="fas fa-fire me-1"></i>Trending
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <?php if (empty($posts)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">No tutorials found</h3>
                    <p class="text-muted">No tutorials available yet.</p>
                </div>
                <?php else: ?>
                <?php foreach ($posts as $post): ?>
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
                            
                            <!-- Voting Section -->
                            <?php if ($isLoggedIn): ?>
                            <?php 
                            // Get user's current vote once to avoid multiple database calls
                            $currentUserVote = $vote->getUserVote($post['id'], $user->getCurrentUser()['id']);
                            $userVoteType = $currentUserVote ? $currentUserVote['vote_type'] : '';
                            ?>
                            <div class="mt-3">
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
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-chart-line me-1"></i>
                                            <?php echo $post['vote_score'] ?? 0; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="mt-3">
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
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="row">
                <div class="col-12">
                    <nav aria-label="Posts pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="posts.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sortBy; ?>">Previous</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="posts.php?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="posts.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sortBy; ?>">Next</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h3 class="mb-4">Never Miss a Tutorial</h3>
                    <p class="text-muted mb-4">Subscribe to our newsletter and get the latest tutorials delivered to your inbox.</p>
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
    <?php include 'includes/scripts.php'; ?> 