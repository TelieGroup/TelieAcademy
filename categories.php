<?php
require_once 'includes/Post.php';
require_once 'includes/Category.php';
require_once 'includes/User.php';
require_once 'includes/Tag.php'; // Added Tag.php

// Initialize classes
$post = new Post();
$category = new Category();
$user = new User();
$tag = new Tag(); // Initialize Tag class

// Check if user is premium
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

// Set page variables for head component
$pageTitle = $currentCategory ? htmlspecialchars($currentCategory['name']) : 'All Categories';
$pageDescription = 'Browse tutorials by category. Learn JavaScript, React, Python, and more.';

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
                            <?php echo htmlspecialchars($currentCategory['name']); ?> Tutorials
                        <?php else: ?>
                            Browse by Category
                        <?php endif; ?>
                    </h1>
                    <?php if ($currentCategory): ?>
                    <p class="lead text-muted"><?php echo htmlspecialchars($currentCategory['description']); ?></p>
                    <p class="text-muted"><?php echo $currentCategory['post_count']; ?> tutorials available</p>
                    <?php else: ?>
                    <p class="lead text-muted">Explore tutorials organized by topic and skill level</p>
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
                        <a href="categories.php" class="btn <?php echo !$selectedCategory ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All Categories
                        </a>
                        <?php foreach ($categories as $cat): ?>
                        <a href="categories.php?category=<?php echo $cat['slug']; ?>" 
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

    <!-- Banner Ads Section -->
    <section class="py-4">
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
                    <h3 class="text-muted">No tutorials found</h3>
                    <p class="text-muted">No tutorials available in this category yet.</p>
                    <a href="categories.php" class="btn btn-primary">Browse All Categories</a>
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
                    <h3 class="mb-4">Stay Updated with Latest Tutorials</h3>
                    <p class="text-muted mb-4">Get notified when we publish new tutorials in your favorite categories.</p>
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