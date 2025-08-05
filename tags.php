<?php
require_once 'includes/Post.php';
require_once 'includes/Tag.php';
require_once 'includes/User.php';

$post = new Post();
$tag = new Tag();
$user = new User();

$isPremium = $user->isPremium();
$selectedTag = isset($_GET['tag']) ? $_GET['tag'] : '';
$allTags = $tag->getAllTags();
$popularTags = $tag->getPopularTags(10);

if ($selectedTag) {
    $posts = $post->getPostsByTag($selectedTag, $isPremium);
    $currentTag = $tag->getTagWithPostCount($selectedTag);
} else {
    $posts = $post->getAllPosts(null, 0, $isPremium);
    $currentTag = null;
}

// Set page variables for head component
$pageTitle = $currentTag ? htmlspecialchars($currentTag['name']) : 'All Tags';
$pageDescription = 'Explore tutorials by specific topics and technologies. Browse tutorials by tags.';

include 'includes/head.php';
?>
    <?php include 'includes/header.php'; ?>

    <section class="py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <?php if ($currentTag): ?>
                            #<?php echo htmlspecialchars($currentTag['name']); ?> Tutorials
                        <?php else: ?>
                            Browse by Tags
                        <?php endif; ?>
                    </h1>
                    <?php if ($currentTag): ?>
                    <p class="text-muted"><?php echo $currentTag['post_count']; ?> tutorials tagged with "<?php echo htmlspecialchars($currentTag['name']); ?>"</p>
                    <?php else: ?>
                    <p class="lead text-muted">Explore tutorials by specific topics and technologies</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h3 class="mb-4">Popular Tags</h3>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="tags.php" class="btn <?php echo !$selectedTag ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All Tags
                        </a>
                        <?php foreach ($popularTags as $tagItem): ?>
                        <a href="tags.php?tag=<?php echo $tagItem['slug']; ?>" 
                           class="btn <?php echo $selectedTag === $tagItem['slug'] ? 'btn-primary' : 'btn-outline-primary'; ?>"
                           style="<?php echo $selectedTag !== $tagItem['slug'] ? 'border-color: ' . htmlspecialchars($tagItem['color'] ?? '#007bff') . '; color: ' . htmlspecialchars($tagItem['color'] ?? '#007bff') . ';' : ''; ?>">
                            #<?php echo htmlspecialchars($tagItem['name']); ?>
                            <span class="badge bg-light text-dark ms-1"><?php echo $tagItem['post_count']; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row" id="postsContainer">
                <?php if (empty($posts)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">No tutorials found</h3>
                    <p class="text-muted">No tutorials available with this tag yet.</p>
                    <a href="tags.php" class="btn btn-primary">Browse All Tags</a>
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
                    <h3 class="mb-4">Get Notified About New Tutorials</h3>
                    <p class="text-muted mb-4">Subscribe to our newsletter and never miss a tutorial on your favorite topics.</p>
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