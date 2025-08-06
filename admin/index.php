<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Post.php';
require_once '../includes/Category.php';
require_once '../includes/Comment.php';
require_once '../includes/Newsletter.php'; // Added for newsletter statistics
require_once '../includes/Media.php'; // Added for media statistics

$user = new User();
$post = new Post();
$category = new Category();
$comment = new Comment();

// Check if user is logged in and is admin
if (!$user->isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$currentUser = $user->getCurrentUser();
if (!$currentUser || !$currentUser['is_admin']) {
    header('Location: ../index.php');
    exit;
}

// Set page variables for head component
$pageTitle = 'Admin Dashboard';
$pageDescription = 'Manage your blog content and settings';

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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="posts.php?action=add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>New Post
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Posts
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $post->getPostCount(true); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Categories
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo count($category->getAllCategories()); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-folder fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Comments
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $comment = new Comment();
                                        echo $comment->getTotalCommentCount(); 
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-comments fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Users
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $user = new User();
                                        echo $user->getUserCount(); 
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Newsletter Subscribers
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $newsletter = new Newsletter();
                                        echo $newsletter->getSubscriberCount(); 
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Media Files
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $media = new Media();
                                        echo $media->getTotalMediaCount(); 
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-images fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Posts -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Posts</h6>
                            <a href="posts.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get recent posts (limit to 5)
                                        $recentPosts = $post->getAllPostsForAdmin(5);
                                        if (empty($recentPosts)):
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No posts found. <a href="posts.php?action=add">Create your first post</a></p>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($recentPosts as $postItem): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($postItem['title']); ?></strong>
                                                <?php if ($postItem['is_premium']): ?>
                                                    <span class="badge bg-warning ms-1">Premium</span>
                                                <?php endif; ?>
                                                <?php if ($postItem['is_featured']): ?>
                                                    <span class="badge bg-info ms-1">Featured</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($postItem['category_name'] ?? 'Uncategorized'); ?></td>
                                            <td>
                                                <?php if ($postItem['status'] === 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($postItem['created_at'])); ?></td>
                                            <td>
                                                <a href="posts.php?action=edit&id=<?php echo $postItem['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePost(<?php echo $postItem['id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?>

<script>
// Delete post function
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'posts.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const postIdInput = document.createElement('input');
        postIdInput.type = 'hidden';
        postIdInput.name = 'post_id';
        postIdInput.value = postId;
        
        form.appendChild(actionInput);
        form.appendChild(postIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script> 