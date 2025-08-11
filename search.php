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

// Get search query
$searchQuery = $_GET['q'] ?? '';
$searchQuery = trim($searchQuery);

// Get search results
$searchResults = [];
$totalResults = 0;

if (!empty($searchQuery)) {
    $searchResults = $post->searchPosts($searchQuery, $isPremium);
    $totalResults = count($searchResults);
}

// Set page variables for head component
$pageTitle = 'Search Results';
$pageDescription = 'Search results for: ' . htmlspecialchars($searchQuery);

include 'includes/head.php';
?>
    <?php include 'includes/header.php'; ?>

    <!-- Search Results Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="mb-2">Search Results</h1>
                            <?php if (!empty($searchQuery)): ?>
                                <p class="text-muted mb-0">
                                    Found <?php echo $totalResults; ?> result<?php echo $totalResults != 1 ? 's' : ''; ?> for 
                                    "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"
                                </p>
                            <?php endif; ?>
                        </div>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>

                    <!-- Search Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="search.php" class="row g-3">
                                <div class="col-md-8">
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           name="q" 
                                           value="<?php echo htmlspecialchars($searchQuery); ?>" 
                                           placeholder="Search for tutorials, topics, or keywords..."
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($searchQuery)): ?>
                        <?php if ($totalResults > 0): ?>
                            <!-- Search Results -->
                            <div class="row">
                                <?php foreach ($searchResults as $result): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="post-card">
                                        <div class="card-badges">
                                            <span class="category-badge"><?php echo htmlspecialchars($result['category_name']); ?></span>
                                            <?php if ($result['is_premium']): ?>
                                            <span class="premium-badge">
                                                <i class="fas fa-crown"></i>Premium
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="post.php?slug=<?php echo $result['slug']; ?>">
                                                    <?php echo htmlspecialchars($result['title']); ?>
                                                </a>
                                            </h5>
                                            
                                            <p class="card-text">
                                                <?php echo htmlspecialchars($result['excerpt']); ?>
                                            </p>
                                            
                                            <div class="card-meta">
                                                <div class="meta-item">
                                                    <i class="fas fa-user"></i>
                                                    <span><?php echo htmlspecialchars($result['author_name']); ?></span>
                                                </div>
                                                <div class="meta-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <span><?php echo date('M j, Y', strtotime($result['published_at'])); ?></span>
                                                </div>
                                            </div>
                                            
                                            <?php if ($result['tags']): ?>
                                            <div class="tags-container">
                                                <?php 
                                                $tags = explode(',', $result['tags']);
                                                foreach (array_slice($tags, 0, 3) as $tagItem): 
                                                    $tagName = trim($tagItem);
                                                    if (!empty($tagName)):
                                                        $tagData = $tag->getTagByName($tagName);
                                                        $tagColor = $tagData ? $tagData['color'] : '#6c757d';
                                                        $tagSlug = $tagData ? $tagData['slug'] : strtolower(str_replace(' ', '-', $tagName));
                                                ?>
                                                <a href="tags.php?tag=<?php echo urlencode($tagSlug); ?>" class="tag-badge" style="background-color: <?php echo htmlspecialchars($tagColor); ?>; color: white;">
                                                    <?php echo htmlspecialchars($tagName); ?>
                                                </a>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Voting Section -->
                                            <?php if ($isLoggedIn): ?>
                                            <?php 
                                            $currentUserVote = $vote->getUserVote($result['id'], $currentUser['id']);
                                            $userVoteType = $currentUserVote ? $currentUserVote['vote_type'] : '';
                                            ?>
                                            <div class="vote-section">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="vote-buttons">
                                                        <button class="btn btn-sm <?php echo $userVoteType === 'upvote' ? 'btn-success' : 'btn-outline-success'; ?> vote-btn" 
                                                                data-post-id="<?php echo $result['id']; ?>" 
                                                                data-vote-type="upvote" 
                                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                                title="Upvote">
                                                            <i class="fas fa-thumbs-up"></i>
                                                            <span class="vote-count ms-1"><?php echo $result['upvotes'] ?? 0; ?></span>
                                                        </button>
                                                        <button class="btn btn-sm <?php echo $userVoteType === 'downvote' ? 'btn-danger' : 'btn-outline-danger'; ?> vote-btn" 
                                                                data-post-id="<?php echo $result['id']; ?>" 
                                                                data-vote-type="downvote" 
                                                                data-current-vote="<?php echo $userVoteType; ?>"
                                                                title="Downvote">
                                                            <i class="fas fa-thumbs-down"></i>
                                                            <span class="vote-count ms-1"><?php echo $result['downvotes'] ?? 0; ?></span>
                                                        </button>
                                                    </div>
                                                    <div class="vote-score">
                                                        <span class="badge">
                                                            <i class="fas fa-chart-line me-1"></i>
                                                            <?php echo $result['vote_score'] ?? 0; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <div class="vote-section">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="vote-stats">
                                                        <small class="text-muted">
                                                            <i class="fas fa-thumbs-up text-success me-1"></i><?php echo $result['upvotes'] ?? 0; ?>
                                                            <i class="fas fa-thumbs-down text-danger ms-2 me-1"></i><?php echo $result['downvotes'] ?? 0; ?>
                                                        </small>
                                                    </div>
                                                    <div class="vote-score">
                                                        <span class="badge">
                                                            <i class="fas fa-chart-line me-1"></i>
                                                            <?php echo $result['vote_score'] ?? 0; ?>
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
                        <?php else: ?>
                            <!-- No Results -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-search fa-4x text-muted"></i>
                                </div>
                                <h3 class="mb-3">No results found</h3>
                                <p class="text-muted mb-4">
                                    We couldn't find any tutorials matching "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"
                                </p>
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Search Tips:</h5>
                                                <ul class="text-start text-muted">
                                                    <li>Check your spelling</li>
                                                    <li>Try different keywords</li>
                                                    <li>Use more general terms</li>
                                                    <li>Browse categories instead</li>
                                                </ul>
                                                <div class="mt-3">
                                                    <a href="categories.php" class="btn btn-outline-primary me-2">Browse Categories</a>
                                                    <a href="tags.php" class="btn btn-outline-secondary">Browse Tags</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Initial Search State -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-search fa-4x text-primary"></i>
                            </div>
                            <h3 class="mb-3">Search Tutorials</h3>
                            <p class="text-muted mb-4">
                                Find the perfect tutorial for your learning journey. Search by topic, technology, or keyword.
                            </p>
                            <div class="row justify-content-center">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Popular Search Terms:</h5>
                                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                                <a href="search.php?q=javascript" class="badge bg-primary text-decoration-none">JavaScript</a>
                                                <a href="search.php?q=react" class="badge bg-primary text-decoration-none">React</a>
                                                <a href="search.php?q=python" class="badge bg-primary text-decoration-none">Python</a>
                                                <a href="search.php?q=web+development" class="badge bg-primary text-decoration-none">Web Development</a>
                                                <a href="search.php?q=databases" class="badge bg-primary text-decoration-none">Databases</a>
                                                <a href="search.php?q=api" class="badge bg-primary text-decoration-none">API</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/modals.php'; ?>

    <?php include 'includes/scripts.php'; ?> 