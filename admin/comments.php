<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Comment.php';

$user = new User();
$comment = new Comment();

// Check if user is logged in and is admin
if (!$user->isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$currentUser = $user->getCurrentUser();
if (!$currentUser || !$currentUser['is_premium']) {
    header('Location: ../index.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? $action;
    
    if ($postAction === 'update_status' && isset($_POST['comment_id'])) {
        try {
            $commentId = $_POST['comment_id'];
            $newStatus = $_POST['status'] ?? 'pending';
            
            $result = $comment->updateCommentStatus($commentId, $newStatus);
            
            if ($result['success']) {
                header('Location: comments.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error updating comment: ' . $e->getMessage();
        }
    } elseif ($postAction === 'delete' && isset($_POST['comment_id'])) {
        try {
            $commentId = $_POST['comment_id'];
            
            $result = $comment->deleteComment($commentId);
            
            if ($result['success']) {
                header('Location: comments.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error deleting comment: ' . $e->getMessage();
        }
    }
}

// Set page variables for head component
$pageTitle = 'Manage Comments';
$pageDescription = 'Manage blog comments and moderation';

include '../includes/head.php';
?>
<!-- Admin CSS -->
<link rel="stylesheet" href="admin.css">
<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Comments</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="comments.php" class="btn btn-sm <?php echo $status === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                        <a href="comments.php?status=pending" class="btn btn-sm <?php echo $status === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending</a>
                        <a href="comments.php?status=approved" class="btn btn-sm <?php echo $status === 'approved' ? 'btn-primary' : 'btn-outline-primary'; ?>">Approved</a>
                        <a href="comments.php?status=spam" class="btn btn-sm <?php echo $status === 'spam' ? 'btn-primary' : 'btn-outline-primary'; ?>">Spam</a>
                    </div>
                </div>
            </div>

            <!-- Comments List -->
            <div class="row">
                <div class="col-md-8">
                    <?php
                    if ($status === 'all') {
                        $comments = $comment->getAllCommentsForAdmin();
                    } else {
                        $comments = $comment->getCommentsByStatus($status);
                    }
                    ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Comment</th>
                                    <th>Author</th>
                                    <th>Post</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($comments)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-comments fa-2x mb-2"></i>
                                        <p>No comments found.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($comments as $commentItem): ?>
                                <tr>
                                    <td>
                                        <div class="comment-content">
                                            <p class="mb-1"><?php echo htmlspecialchars(substr($commentItem['content'], 0, 100)); ?><?php echo strlen($commentItem['content']) > 100 ? '...' : ''; ?></p>
                                            <?php if (strlen($commentItem['content']) > 100): ?>
                                            <button class="btn btn-sm btn-link p-0" onclick="showFullComment(<?php echo $commentItem['id']; ?>)">Read more</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($commentItem['username']): ?>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($commentItem['username']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($commentItem['guest_name']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../post.php?slug=<?php echo $commentItem['post_slug']; ?>" target="_blank" class="text-decoration-none">
                                            <?php echo htmlspecialchars($commentItem['post_title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($commentItem['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($commentItem['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Spam</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($commentItem['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($commentItem['status'] !== 'approved'): ?>
                                            <button class="btn btn-outline-success" onclick="updateCommentStatus(<?php echo $commentItem['id']; ?>, 'approved')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($commentItem['status'] !== 'pending'): ?>
                                            <button class="btn btn-outline-warning" onclick="updateCommentStatus(<?php echo $commentItem['id']; ?>, 'pending')">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($commentItem['status'] !== 'spam'): ?>
                                            <button class="btn btn-outline-danger" onclick="updateCommentStatus(<?php echo $commentItem['id']; ?>, 'spam')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-danger" onclick="deleteComment(<?php echo $commentItem['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Comment Statistics</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $stats = $comment->getCommentStatistics();
                            ?>
                            <div class="mb-3">
                                <label class="form-label">Total Comments</label>
                                <h4 class="text-primary mb-0"><?php echo $stats['total_comments'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pending Comments</label>
                                <h4 class="text-warning mb-0"><?php echo $stats['pending_comments'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Approved Comments</label>
                                <h4 class="text-success mb-0"><?php echo $stats['approved_comments'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Recent Comment</label>
                                <p class="text-muted mb-0 small"><?php echo $stats['recent_comment'] ?? 'No comments'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="commentModalBody">
                <!-- Comment content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Update comment status
function updateCommentStatus(commentId, status) {
    if (confirm('Are you sure you want to update this comment status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'comments.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'update_status';
        
        const commentIdInput = document.createElement('input');
        commentIdInput.type = 'hidden';
        commentIdInput.name = 'comment_id';
        commentIdInput.value = commentId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        
        form.appendChild(actionInput);
        form.appendChild(commentIdInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Delete comment
function deleteComment(commentId) {
    if (confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'comments.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const commentIdInput = document.createElement('input');
        commentIdInput.type = 'hidden';
        commentIdInput.name = 'comment_id';
        commentIdInput.value = commentId;
        
        form.appendChild(actionInput);
        form.appendChild(commentIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Show full comment
function showFullComment(commentId) {
    // This would typically load the full comment via AJAX
    // For now, we'll just show an alert
    alert('Full comment view would be implemented here for comment ID: ' + commentId);
}
</script>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?> 