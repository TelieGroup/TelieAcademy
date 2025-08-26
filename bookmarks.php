<?php
require_once 'config/session.php';
require_once 'includes/User.php';
require_once 'includes/Bookmark.php';

$user = new User();
$bookmark = new Bookmark();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$currentUser = $user->getCurrentUser();

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get user's bookmarks
$bookmarks = $bookmark->getUserBookmarks($currentUser['id'], $limit, $offset);
$totalCount = $bookmark->getUserBookmarkCount($currentUser['id']);
$totalPages = ceil($totalCount / $limit);

// Set page variables for head component
$pageTitle = 'My Bookmarks';
$pageDescription = 'View and manage your bookmarked posts';

include 'includes/head.php';
?>
<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-10 col-xl-11 mx-auto">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-bookmark text-primary me-2"></i>
                            My Bookmarks
                        </h1>
                        <p class="text-muted">
                            <?php echo $totalCount; ?> bookmarked post<?php echo $totalCount !== 1 ? 's' : ''; ?>
                        </p>
                    </div>
                    <div>
                        <a href="index" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>

            <?php if (empty($bookmarks)): ?>
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-bookmark fa-3x text-muted mb-3"></i>
                        <h3>No bookmarks yet</h3>
                        <p class="text-muted mb-4">
                            Start bookmarking posts you want to read later. 
                            Your bookmarks will appear here for easy access.
                        </p>
                        <a href="index" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Explore Posts
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Bookmarks Grid -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                    <?php foreach ($bookmarks as $bookmarkData): ?>
                        <div class="col">
                            <div class="card h-100 bookmark-card">
                                <!-- Post Image -->
                                <?php if (!empty($bookmarkData['featured_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($bookmarkData['featured_image']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($bookmarkData['title']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Card Body -->
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($bookmarkData['category_name']); ?></span>
                                    </div>
                                    
                                    <h5 class="card-title">
                                        <a href="post?slug=<?php echo htmlspecialchars($bookmarkData['slug']); ?>" 
                                           class="text-decoration-none">
                                            <?php echo htmlspecialchars($bookmarkData['title']); ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted flex-grow-1">
                                        <?php echo htmlspecialchars(substr($bookmarkData['excerpt'], 0, 100)) . (strlen($bookmarkData['excerpt']) > 100 ? '...' : ''); ?>
                                    </p>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center text-muted small mb-2">
                                            <span>
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($bookmarkData['author_name']); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M j, Y', strtotime($bookmarkData['published_at'])); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="post?slug=<?php echo htmlspecialchars($bookmarkData['slug']); ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>Read
                                            </a>
                                            <button class="btn btn-outline-danger btn-sm remove-bookmark-btn" 
                                                    data-post-id="<?php echo $bookmarkData['post_id']; ?>"
                                                    title="Remove bookmark">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Bookmark Date -->
                                <div class="card-footer text-muted small">
                                    <i class="fas fa-bookmark text-primary me-1"></i>
                                    Bookmarked on <?php echo date('M j, Y', strtotime($bookmarkData['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Bookmarks pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/scripts.php'; ?>

<script>
// Initialize bookmark functionality
document.addEventListener('DOMContentLoaded', function() {
    // Remove bookmark functionality
    document.querySelectorAll('.remove-bookmark-btn').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const card = this.closest('.bookmark-card');
            
            if (confirm('Are you sure you want to remove this bookmark?')) {
                removeBookmark(postId, card);
            }
        });
    });
});

async function removeBookmark(postId, card) {
    try {
        const response = await fetch('api/bookmarks.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove the card with animation
            card.style.transition = 'all 0.3s ease';
            card.style.transform = 'scale(0.8)';
            card.style.opacity = '0';
            
            setTimeout(() => {
                card.remove();
                
                // Update bookmark count
                const countElement = document.querySelector('.text-muted');
                const currentCount = parseInt(countElement.textContent);
                countElement.textContent = (currentCount - 1) + ' bookmarked post' + (currentCount - 1 !== 1 ? 's' : '');
                
                // Check if no more bookmarks
                const remainingCards = document.querySelectorAll('.bookmark-card');
                if (remainingCards.length === 0) {
                    location.reload(); // Reload to show empty state
                }
            }, 300);
            
            showAlert('Bookmark removed successfully', 'success');
        } else {
            showAlert('Failed to remove bookmark', 'error');
        }
    } catch (error) {
        console.error('Error removing bookmark:', error);
        showAlert('An error occurred while removing the bookmark', 'error');
    }
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
