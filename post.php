<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/Comment.php';
require_once 'includes/User.php';
require_once 'includes/Tag.php';
require_once 'includes/Vote.php';

$post = new Post();
$comment = new Comment();
$user = new User();
$tag = new Tag();
$vote = new Vote();

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

$postData = $post->getPostBySlug($slug, $isPremium);
$comments = $comment->getCommentsByPost($postData['id']);

// Get vote statistics
$voteStats = $vote->getPostVoteStats($postData['id']);
$userVote = $isLoggedIn ? $vote->getUserVote($postData['id'], $user->getCurrentUser()['id']) : null;

// Set page variables for head component
$pageTitle = htmlspecialchars($postData['title']);
$pageDescription = htmlspecialchars($postData['excerpt']);

include 'includes/head.php';
?>
<?php include 'includes/header.php'; ?>

<!-- Reading Progress Bar -->
<div class="reading-progress-bar" id="readingProgressBar"></div>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-8 col-xl-9">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="breadcrumb-nav mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="categories.php?category=<?php echo $postData['category_slug']; ?>"><?php echo htmlspecialchars($postData['category_name']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($postData['title']); ?></li>
                </ol>
            </nav>

            <!-- Post Header -->
            <header class="post-header mb-5">
                <div class="post-meta mb-3">
                    <span class="category-badge"><?php echo htmlspecialchars($postData['category_name']); ?></span>
                    <?php if ($postData['is_premium']): ?>
                    <span class="premium-badge">
                        <i class="fas fa-crown"></i>Premium
                    </span>
                    <?php endif; ?>
                    <span class="reading-time">
                        <i class="fas fa-clock"></i>
                        <?php 
                        $wordCount = str_word_count(strip_tags($postData['content']));
                        $readingTime = ceil($wordCount / 200); // Average reading speed: 200 words per minute
                        echo $readingTime . ' min read';
                        ?>
                    </span>
                </div>
                
                <h1 class="post-title"><?php echo htmlspecialchars($postData['title']); ?></h1>
                <p class="post-excerpt"><?php echo htmlspecialchars($postData['excerpt']); ?></p>
                
                <div class="post-info">
                    <div class="post-author">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($postData['author_name']); ?></span>
                    </div>
                    <div class="post-date">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('M j, Y', strtotime($postData['published_at'])); ?></span>
                    </div>
                    <div class="post-views">
                        <i class="fas fa-eye"></i>
                        <span><?php echo number_format($postData['views'] ?? rand(100, 5000)); ?> views</span>
                    </div>
                </div>
                
                <?php if ($postData['tags']): ?>
                <div class="post-tags">
                    <i class="fas fa-tags"></i>
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

            <!-- Post Content -->
            <article class="post-content mb-5">
                <?php echo $postData['content']; ?>
            </article>

            <!-- Post Actions -->
            <div class="post-actions mb-5">
                <div class="action-buttons">
                    <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="sharePost()">
                        <i class="fas fa-share"></i> Share
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="copyPostLink()">
                        <i class="fas fa-link"></i> Copy Link
                    </button>
                </div>
            </div>

            <!-- Voting Section -->
            <div class="voting-section mb-5">
                <div class="voting-container">
                    <div class="voting-content">
                        <div class="voting-info">
                            <h6>Was this tutorial helpful?</h6>
                            <p>Help others discover great content by voting</p>
                        </div>
                        <div class="voting-actions">
                            <?php if ($isLoggedIn): ?>
                            <?php 
                            $userVoteType = $userVote ? $userVote['vote_type'] : '';
                            ?>
                            <div class="vote-buttons">
                                <button class="btn <?php echo $userVoteType === 'upvote' ? 'btn-success' : 'btn-outline-success'; ?> vote-btn" 
                                        data-post-id="<?php echo $postData['id']; ?>" 
                                        data-vote-type="upvote" 
                                        data-current-vote="<?php echo $userVoteType; ?>"
                                        title="This tutorial was helpful">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="vote-count"><?php echo $voteStats['upvotes'] ?? 0; ?></span>
                                </button>
                                <button class="btn <?php echo $userVoteType === 'downvote' ? 'btn-danger' : 'btn-outline-danger'; ?> vote-btn" 
                                        data-post-id="<?php echo $postData['id']; ?>" 
                                        data-vote-type="downvote" 
                                        data-current-vote="<?php echo $userVoteType; ?>"
                                        title="This tutorial needs improvement">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span class="vote-count"><?php echo $voteStats['downvotes'] ?? 0; ?></span>
                                </button>
                            </div>
                            <div class="vote-score">
                                <span class="score-badge">
                                    <i class="fas fa-chart-line"></i>
                                    Score: <?php echo $voteStats['vote_score'] ?? 0; ?>
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="vote-stats">
                                <span class="stat-item">
                                    <i class="fas fa-thumbs-up text-success"></i>
                                    <?php echo $voteStats['upvotes'] ?? 0; ?>
                                </span>
                                <span class="stat-item">
                                    <i class="fas fa-thumbs-down text-danger"></i>
                                    <?php echo $voteStats['downvotes'] ?? 0; ?>
                                </span>
                            </div>
                            <div class="vote-score">
                                <span class="score-badge">
                                    <i class="fas fa-chart-line"></i>
                                    Score: <?php echo $voteStats['vote_score'] ?? 0; ?>
                                </span>
                            </div>
                            <div class="login-prompt">
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <i class="fas fa-sign-in-alt"></i>Login to Vote
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <section class="comments-section">
                <div class="comments-header">
                    <h3 class="mb-3">
                        <i class="fas fa-comments"></i>
                        Comments (<?php echo count($comments); ?>)
                    </h3>
                    <p class="text-muted">Join the conversation and share your thoughts</p>
                </div>
                
                <div class="comment-form-container mb-4">
                    <h6>Leave a Comment</h6>
                    <form id="commentForm">
                        <input type="hidden" id="postId" value="<?php echo $postData['id']; ?>">
                        <div class="mb-3">
                            <textarea class="form-control" id="commentContent" rows="4" placeholder="Share your thoughts, ask questions, or provide feedback..." required></textarea>
                        </div>
                        <div id="commentMessage" class="alert" style="display: none;"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Your comment will be visible to everyone</small>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Post Comment
                            </button>
                        </div>
                    </form>
                </div>

                <div id="commentsList">
                    <?php if (empty($comments)): ?>
                    <div class="no-comments">
                        <div class="no-comments-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h5>No comments yet</h5>
                        <p>Be the first to share your thoughts and start the conversation!</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-author-info">
                                <strong class="comment-author"><?php echo htmlspecialchars($comment['username'] ?: $comment['guest_name']); ?></strong>
                                <span class="comment-date"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                            </div>
                            <div class="comment-actions">
                                <button class="btn btn-sm btn-outline-secondary" onclick="replyToComment(<?php echo $comment['id']; ?>)">
                                    <i class="fas fa-reply"></i> Reply
                                </button>
                            </div>
                        </div>
                        <div class="comment-content">
                            <?php echo htmlspecialchars($comment['content']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 col-xl-3">
            <div class="sticky-sidebar" style="top: 100px;">
                <!-- Table of Contents -->
                <div class="sidebar-widget mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-list"></i> Table of Contents
                    </h5>
                    <div class="toc-container">
                        <ul class="toc-list" id="tableOfContents">
                            <!-- Generated by JavaScript -->
                        </ul>
                    </div>
                </div>

                <!-- About This Tutorial -->
                <div class="sidebar-widget mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle"></i> About This Tutorial
                    </h5>
                    <p class="small text-muted mb-3"><?php echo htmlspecialchars($postData['excerpt']); ?></p>
                    <div class="tutorial-stats">
                        <div class="stat-item">
                            <i class="fas fa-clock text-primary"></i>
                            <span><?php echo $readingTime; ?> min read</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-eye text-info"></i>
                            <span><?php echo number_format($postData['views'] ?? rand(100, 5000)); ?> views</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-comments text-success"></i>
                            <span><?php echo count($comments); ?> comments</span>
                        </div>
                    </div>
                    <div class="tutorial-badges mt-3">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($postData['category_name']); ?></span>
                        <?php if ($postData['is_premium']): ?>
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-crown me-1"></i>Premium
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Newsletter Sidebar Widget -->
                <div class="sidebar-widget mb-4">
                    <h6 class="mb-3 text-primary">
                        <i class="fas fa-envelope me-2"></i>Stay Updated
                    </h6>
                    <p class="small text-muted mb-3">Get notified about new tutorials and exclusive content.</p>
                    <div class="newsletter-form">
                        <input type="email" class="form-control mb-2" id="sidebarNewsletterEmail" placeholder="Your email address">
                        <button class="btn btn-primary btn-sm w-100" type="button" id="sidebarNewsletterSubmit">
                            <i class="fas fa-paper-plane me-1"></i>Subscribe
                        </button>
                        <div id="sidebarNewsletterMessage" class="alert mt-2" style="display: none;"></div>
                    </div>
                </div>

                <!-- Related Posts -->
                <div class="sidebar-widget mb-4">
                    <h6 class="mb-3 text-primary">
                        <i class="fas fa-bookmark me-2"></i>Related Tutorials
                    </h6>
                    <div class="related-posts">
                        <div class="related-post-item">
                            <a href="#" class="related-post-link">
                                <h6 class="related-post-title">Getting Started with Web Development</h6>
                                <small class="text-muted">5 min read</small>
                            </a>
                        </div>
                        <div class="related-post-item">
                            <a href="#" class="related-post-link">
                                <h6 class="related-post-title">Advanced CSS Techniques</h6>
                                <small class="text-muted">8 min read</small>
                            </a>
                        </div>
                        <div class="related-post-item">
                            <a href="#" class="related-post-link">
                                <h6 class="related-post-title">JavaScript Best Practices</h6>
                                <small class="text-muted">6 min read</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Newsletter Section -->
<section class="newsletter-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="newsletter-content">
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
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/modals.php'; ?>
<?php include 'includes/scripts.php'; ?>

<script>
// Post Page Enhancements
document.addEventListener('DOMContentLoaded', function() {
    initializeReadingProgressBar();
    generateTableOfContents();
    initializePostActions();
    initializeCommentEnhancements();
});

// Reading Progress Bar
function initializeReadingProgressBar() {
    const progressBar = document.getElementById('readingProgressBar');
    if (!progressBar) return;

    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        progressBar.style.width = scrollPercent + '%';
    });
}

// Generate Table of Contents
function generateTableOfContents() {
    const tocContainer = document.getElementById('tableOfContents');
    if (!tocContainer) return;

    const headings = document.querySelectorAll('.post-content h1, .post-content h2, .post-content h3, .post-content h4, .post-content h5, .post-content h6');
    
    if (headings.length === 0) {
        tocContainer.innerHTML = '<li><span class="text-muted">No headings found</span></li>';
        return;
    }

    const tocList = document.createElement('ul');
    tocList.className = 'toc-list';

    headings.forEach((heading, index) => {
        // Add ID to heading if it doesn't have one
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }

        const listItem = document.createElement('li');
        const link = document.createElement('a');
        link.href = '#' + heading.id;
        link.textContent = heading.textContent;
        link.className = 'toc-link';
        
        // Add indentation based on heading level
        const level = parseInt(heading.tagName.charAt(1));
        link.style.paddingLeft = (level - 1) * 20 + 'px';
        
        listItem.appendChild(link);
        tocList.appendChild(listItem);
    });

    tocContainer.appendChild(tocList);

    // Smooth scrolling for TOC links
    tocContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('toc-link')) {
            e.preventDefault();
            const targetId = e.target.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const headerHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = targetElement.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });

    // Highlight active TOC item while scrolling
    window.addEventListener('scroll', function() {
        highlightActiveTOCItem();
    });
}

// Highlight active TOC item
function highlightActiveTOCItem() {
    const headings = document.querySelectorAll('.post-content h1, .post-content h2, .post-content h3, .post-content h4, .post-content h5, .post-content h6');
    const tocLinks = document.querySelectorAll('.toc-link');
    
    if (headings.length === 0 || tocLinks.length === 0) return;

    const scrollPosition = window.pageYOffset + 100;
    
    let currentHeading = null;
    headings.forEach(heading => {
        if (heading.offsetTop <= scrollPosition) {
            currentHeading = heading;
        }
    });

    tocLinks.forEach(link => {
        link.classList.remove('active');
        if (currentHeading && link.getAttribute('href') === '#' + currentHeading.id) {
            link.classList.add('active');
        }
    });
}

// Initialize Post Actions
function initializePostActions() {
    // Share functionality
    if (typeof navigator.share !== 'undefined') {
        const shareBtn = document.querySelector('[onclick="sharePost()"]');
        if (shareBtn) {
            shareBtn.onclick = sharePost;
        }
    }
}

// Share Post
function sharePost() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: document.querySelector('.post-excerpt')?.textContent || '',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        copyPostLink();
    }
}

// Copy Post Link
function copyPostLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        showToast('Link copied to clipboard!', 'success');
    }).catch(function() {
        showToast('Failed to copy link', 'error');
    });
}

// Initialize Comment Enhancements
function initializeCommentEnhancements() {
    // Add reply functionality
    const replyButtons = document.querySelectorAll('[onclick*="replyToComment"]');
    replyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const commentId = this.getAttribute('onclick').match(/\d+/)[0];
            replyToComment(commentId);
        });
    });
}

// Reply to Comment
function replyToComment(commentId) {
    const commentForm = document.getElementById('commentForm');
    const commentTextarea = document.getElementById('commentContent');
    
    if (commentForm && commentTextarea) {
        commentTextarea.value = `@Comment-${commentId} `;
        commentTextarea.focus();
        commentTextarea.setSelectionRange(commentTextarea.value.length, commentTextarea.value.length);
        
        // Scroll to comment form
        commentForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Show Toast Notification
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    // Set background color based on type
    switch(type) {
        case 'success':
            toast.style.backgroundColor = '#28a745';
            break;
        case 'error':
            toast.style.backgroundColor = '#dc3545';
            break;
        case 'warning':
            toast.style.backgroundColor = '#ffc107';
            toast.style.color = '#212529';
            break;
        default:
            toast.style.backgroundColor = '#17a2b8';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Enhanced Comment Form
function enhanceCommentForm() {
    const commentForm = document.getElementById('commentForm');
    const commentTextarea = document.getElementById('commentContent');
    
    if (!commentForm || !commentTextarea) return;
    
    // Add character counter
    const counter = document.createElement('div');
    counter.className = 'char-counter';
    counter.style.cssText = 'font-size: 0.875rem; color: var(--text-muted); margin-top: 0.5rem;';
    commentForm.appendChild(counter);
    
    function updateCounter() {
        const remaining = 1000 - commentTextarea.value.length;
        counter.textContent = `${remaining} characters remaining`;
        counter.style.color = remaining < 100 ? '#dc3545' : 'var(--text-muted)';
    }
    
    commentTextarea.addEventListener('input', updateCounter);
    updateCounter();
    
    // Add submit button state management
    const submitBtn = commentForm.querySelector('button[type="submit"]');
    if (submitBtn) {
        commentTextarea.addEventListener('input', function() {
            submitBtn.disabled = this.value.trim().length === 0;
        });
    }
}

// Initialize enhanced comment form
document.addEventListener('DOMContentLoaded', function() {
    enhanceCommentForm();
});

// Keyboard Shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Enter to submit comment
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const activeElement = document.activeElement;
        if (activeElement && activeElement.id === 'commentContent') {
            e.preventDefault();
            const commentForm = document.getElementById('commentForm');
            if (commentForm) {
                commentForm.dispatchEvent(new Event('submit'));
            }
        }
    }
    
    // Escape to clear comment form
    if (e.key === 'Escape') {
        const commentTextarea = document.getElementById('commentContent');
        if (commentTextarea && document.activeElement === commentTextarea) {
            commentTextarea.value = '';
            commentTextarea.blur();
        }
    }
});

// Lazy Loading for Images
function initializeLazyLoading() {
    const images = document.querySelectorAll('.post-content img');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => {
            if (img.dataset.src) {
                img.classList.add('lazy');
                imageObserver.observe(img);
            }
        });
    }
}

// Initialize lazy loading
document.addEventListener('DOMContentLoaded', function() {
    initializeLazyLoading();
});

// Print Styles
function addPrintStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            .navbar, .sidebar, .voting-section, .comments-section, .newsletter-section,
            .breadcrumb-nav, .post-actions, .reading-progress-bar {
                display: none !important;
            }
            
            .post-header, .post-content {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .post-title {
                font-size: 2rem !important;
                color: black !important;
                -webkit-text-fill-color: black !important;
            }
            
            .post-content {
                font-size: 1rem !important;
                line-height: 1.6 !important;
            }
            
            body {
                padding: 0 !important;
                margin: 1in !important;
            }
        }
    `;
    document.head.appendChild(style);
}

// Add print styles
document.addEventListener('DOMContentLoaded', function() {
    addPrintStyles();
});
</script> 