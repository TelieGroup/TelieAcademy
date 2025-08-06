<?php
require_once 'includes/Post.php';
require_once 'includes/Comment.php';
require_once 'includes/User.php';
require_once 'includes/Tag.php';

$post = new Post();
$comment = new Comment();
$user = new User();
$tag = new Tag();

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$isPremium = $user->isPremium();

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

$postData = $post->getPostBySlug($slug, $isPremium);
$comments = $comment->getCommentsByPost($postData['id']);

// Set page variables for head component
$pageTitle = htmlspecialchars($postData['title']);
$pageDescription = htmlspecialchars($postData['excerpt']);

include 'includes/head.php';
?>
<?php include 'includes/header.php'; ?>

    <div class="container mt-5 pt-5">
        <div class="row">
            <div class="col-lg-8">
                <header class="mb-4">
                    <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($postData['title']); ?></h1>
                    <p class="lead text-muted"><?php echo htmlspecialchars($postData['excerpt']); ?></p>
                    
                    <div class="d-flex align-items-center text-muted mb-4">
                        <div class="me-4">
                            <i class="fas fa-user me-1"></i>
                            <span><?php echo htmlspecialchars($postData['author_name']); ?></span>
                        </div>
                        <div class="me-4">
                            <i class="fas fa-calendar me-1"></i>
                            <span><?php echo date('M j, Y', strtotime($postData['published_at'])); ?></span>
                        </div>
                        <div class="me-4">
                            <i class="fas fa-folder me-1"></i>
                            <a href="categories.php?category=<?php echo $postData['category_slug']; ?>">
                                <?php echo htmlspecialchars($postData['category_name']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($postData['tags']): ?>
                    <div class="mb-4">
                        <i class="fas fa-tags me-2 text-muted"></i>
                        <?php 
                        $tags = explode(',', $postData['tags']);
                        foreach ($tags as $tagName):
                            $tagName = trim($tagName);
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
                    </div>
                    <?php endif; ?>
                </header>

                <article class="post-content mb-5">
                    <?php echo $postData['content']; ?>
                </article>

                <section class="comments-section">
                    <h3 class="mb-4">Comments (<?php echo count($comments); ?>)</h3>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Leave a Comment</h6>
                            <form id="commentForm">
                                <input type="hidden" id="postId" value="<?php echo $postData['id']; ?>">
                                <div class="mb-3">
                                    <textarea class="form-control" id="commentContent" rows="3" placeholder="Share your thoughts..." required></textarea>
                                </div>
                                <div id="commentMessage" class="alert" style="display: none;"></div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        </div>
                    </div>

                    <div id="commentsList">
                        <?php if (empty($comments)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p>No comments yet. Be the first to share your thoughts!</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment-item card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars($comment['username'] ?: $comment['guest_name']); ?></strong>
                                        <small class="text-muted ms-2"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></small>
                                    </div>
                                </div>
                                <p class="mb-0"><?php echo htmlspecialchars($comment['content']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">About This Tutorial</h5>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted"><?php echo htmlspecialchars($postData['excerpt']); ?></p>
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($postData['category_name']); ?></span>
                                <?php if ($postData['is_premium']): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-crown me-1"></i>Premium
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Newsletter Sidebar Widget -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-envelope me-2"></i>Stay Updated
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">Get notified about new tutorials and exclusive content.</p>
                            <div class="newsletter-form">
                                <input type="email" class="form-control mb-2" id="sidebarNewsletterEmail" placeholder="Your email address">
                                <button class="btn btn-primary btn-sm w-100" type="button" id="sidebarNewsletterSubmit">
                                    <i class="fas fa-paper-plane me-1"></i>Subscribe
                                </button>
                                <div id="sidebarNewsletterMessage" class="alert mt-2" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h3 class="mb-4">Enjoyed This Tutorial?</h3>
                    <p class="text-muted mb-4">Subscribe to our newsletter and get more tutorials like this delivered to your inbox.</p>
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