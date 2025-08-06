<?php
require_once 'config/session.php';
require_once 'includes/User.php';
require_once 'includes/Post.php';
require_once 'includes/Comment.php';

$user = new User();
$post = new Post();
$comment = new Comment();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$currentUser = $user->getCurrentUser();
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? $action;
    
    if ($postAction === 'update_profile') {
        try {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $website = trim($_POST['website'] ?? '');
            
            // Validate input
            if (empty($username)) {
                throw new Exception('Username is required');
            }
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Valid email is required');
            }
            
            // Check if username or email already exists (excluding current user)
            $existingUser = $user->getUserByUsername($username);
            if ($existingUser && $existingUser['id'] != $currentUser['id']) {
                throw new Exception('Username already exists');
            }
            
            $existingEmail = $user->getUserByEmail($email);
            if ($existingEmail && $existingEmail['id'] != $currentUser['id']) {
                throw new Exception('Email already exists');
            }
            
            $result = $user->updateProfile($currentUser['id'], [
                'username' => $username,
                'email' => $email,
                'bio' => $bio,
                'website' => $website
            ]);
            
            if ($result['success']) {
                $successMessage = 'Profile updated successfully!';
                // Refresh current user data
                $currentUser = $user->getCurrentUser();
            } else {
                $errorMessage = $result['message'];
            }
        } catch (Exception $e) {
            $errorMessage = 'Error updating profile: ' . $e->getMessage();
        }
    } elseif ($postAction === 'change_password') {
        try {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($currentPassword)) {
                throw new Exception('Current password is required');
            }
            
            if (empty($newPassword)) {
                throw new Exception('New password is required');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('Passwords do not match');
            }
            
            // Verify current password
            if (!$user->verifyPassword($currentUser['id'], $currentPassword)) {
                throw new Exception('Current password is incorrect');
            }
            
            $result = $user->changePassword($currentUser['id'], $newPassword);
            
            if ($result['success']) {
                $successMessage = 'Password changed successfully!';
            } else {
                $errorMessage = $result['message'];
            }
        } catch (Exception $e) {
            $errorMessage = 'Error changing password: ' . $e->getMessage();
        }
    }
}

// Get user's posts and comments
$userPosts = $post->getPostsByAuthor($currentUser['id']);
$userComments = $comment->getCommentsByUser($currentUser['id']);

// Set page variables for head component
$pageTitle = 'My Profile - ' . htmlspecialchars($currentUser['username']);
$pageDescription = 'Manage your profile and account settings';

include 'includes/head.php';
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-5 pt-5">
    <div class="row">
        <div class="col-lg-8">
            <?php if (isset($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">My Profile</h1>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="showProfileForm()">Edit Profile</button>
                    <button class="btn btn-outline-secondary" onclick="showPasswordForm()">Change Password</button>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="profile-avatar">
                                <i class="fas fa-user-circle fa-5x text-muted"></i>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h4><?php echo htmlspecialchars($currentUser['username']); ?></h4>
                            <p class="text-muted mb-2">
                                <i class="fas fa-envelope me-1"></i>
                                <?php echo htmlspecialchars($currentUser['email']); ?>
                            </p>
                            <?php if (!empty($currentUser['bio'])): ?>
                            <p class="mb-2"><?php echo htmlspecialchars($currentUser['bio']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($currentUser['website'])): ?>
                            <p class="mb-2">
                                <i class="fas fa-globe me-1"></i>
                                <a href="<?php echo htmlspecialchars($currentUser['website']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($currentUser['website']); ?>
                                </a>
                            </p>
                            <?php endif; ?>
                            <p class="text-muted small">
                                <i class="fas fa-calendar me-1"></i>
                                Member since <?php echo date('M j, Y', strtotime($currentUser['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

                         <!-- User Activity -->
             <div class="row">
                 <?php if ($currentUser['is_premium']): ?>
                 <div class="col-md-6">
                     <div class="card mb-4">
                         <div class="card-header">
                             <h6 class="mb-0">
                                 <i class="fas fa-file-alt me-2"></i>My Posts (<?php echo count($userPosts); ?>)
                             </h6>
                         </div>
                         <div class="card-body">
                             <?php if (empty($userPosts)): ?>
                             <p class="text-muted text-center">No posts yet.</p>
                             <?php else: ?>
                             <div class="list-group list-group-flush">
                                 <?php foreach (array_slice($userPosts, 0, 5) as $postItem): ?>
                                 <a href="post.php?slug=<?php echo $postItem['slug']; ?>" class="list-group-item list-group-item-action">
                                     <div class="d-flex w-100 justify-content-between">
                                         <h6 class="mb-1"><?php echo htmlspecialchars($postItem['title']); ?></h6>
                                         <small class="text-muted"><?php echo date('M j', strtotime($postItem['published_at'])); ?></small>
                                     </div>
                                     <small class="text-muted"><?php echo htmlspecialchars($postItem['category_name']); ?></small>
                                 </a>
                                 <?php endforeach; ?>
                             </div>
                             <?php endif; ?>
                         </div>
                     </div>
                 </div>
                 <?php else: ?>
                 <div class="col-md-6">
                     <div class="card mb-4">
                         <div class="card-header">
                             <h6 class="mb-0">
                                 <i class="fas fa-heart me-2"></i>My Favorites
                             </h6>
                         </div>
                         <div class="card-body">
                             <p class="text-muted text-center">No favorites yet.</p>
                             <div class="text-center">
                                 <a href="posts.php" class="btn btn-outline-primary btn-sm">
                                     <i class="fas fa-search me-1"></i>Discover Posts
                                 </a>
                             </div>
                         </div>
                     </div>
                 </div>
                 <?php endif; ?>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-comments me-2"></i>My Comments (<?php echo count($userComments); ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($userComments)): ?>
                            <p class="text-muted text-center">No comments yet.</p>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($userComments, 0, 5) as $commentItem): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <small class="text-muted"><?php echo date('M j', strtotime($commentItem['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1 small"><?php echo htmlspecialchars(substr($commentItem['content'], 0, 100)) . (strlen($commentItem['content']) > 100 ? '...' : ''); ?></p>
                                    <small class="text-muted">on <?php echo htmlspecialchars($commentItem['post_title']); ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                <!-- Account Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Account Statistics
                        </h6>
                    </div>
                                         <div class="card-body">
                         <div class="row text-center">
                             <?php if ($currentUser['is_premium']): ?>
                             <div class="col-6">
                                 <h4 class="text-primary"><?php echo count($userPosts); ?></h4>
                                 <small class="text-muted">Posts</small>
                             </div>
                             <div class="col-6">
                                 <h4 class="text-success"><?php echo count($userComments); ?></h4>
                                 <small class="text-muted">Comments</small>
                             </div>
                             <?php else: ?>
                             <div class="col-6">
                                 <h4 class="text-info"><?php echo count($userComments); ?></h4>
                                 <small class="text-muted">Comments</small>
                             </div>
                             <div class="col-6">
                                 <h4 class="text-warning">0</h4>
                                 <small class="text-muted">Favorites</small>
                             </div>
                             <?php endif; ?>
                         </div>
                     </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                                         <div class="card-body">
                         <div class="d-grid gap-2">
                             <?php if ($currentUser['is_premium']): ?>
                             <a href="admin/posts.php?action=add" class="btn btn-primary btn-sm">
                                 <i class="fas fa-plus me-1"></i>Create New Post
                             </a>
                             <a href="admin/" class="btn btn-outline-warning btn-sm">
                                 <i class="fas fa-cog me-1"></i>Admin Panel
                             </a>
                             <?php else: ?>
                             <a href="posts.php" class="btn btn-outline-secondary btn-sm">
                                 <i class="fas fa-list me-1"></i>View All Posts
                             </a>
                             <a href="categories.php" class="btn btn-outline-secondary btn-sm">
                                 <i class="fas fa-folder me-1"></i>Browse Categories
                             </a>
                             <?php endif; ?>
                         </div>
                     </div>
                </div>

                <!-- Newsletter Section -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-envelope me-2"></i>Stay Updated
                        </h6>
                    </div>
                    <div class="card-body">
                        <form class="newsletter-form">
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-paper-plane me-1"></i>Subscribe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="profile.php">
                <input type="hidden" name="action" value="update_profile">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($currentUser['bio'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($currentUser['website'] ?? ''); ?>" placeholder="https://yourwebsite.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="profile.php">
                <input type="hidden" name="action" value="change_password">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showProfileForm() {
    const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
}

function showPasswordForm() {
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}
</script>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/modals.php'; ?>
<?php include 'includes/scripts.php'; ?> 