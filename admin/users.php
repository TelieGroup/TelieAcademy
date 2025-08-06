<?php
require_once '../config/session.php';
require_once '../includes/User.php';

$user = new User();

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

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? $action;
    
    if ($postAction === 'add') {
        try {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $isPremium = isset($_POST['is_premium']) ? 1 : 0;
            
            // Validate required fields
            if (empty($username)) {
                throw new Exception('Username is required');
            }
            if (empty($email)) {
                throw new Exception('Email is required');
            }
            if (empty($password)) {
                throw new Exception('Password is required');
            }
            
            $result = $user->createUser([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'is_premium' => $isPremium
            ]);
            
            if ($result['success']) {
                header('Location: users.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error creating user: ' . $e->getMessage();
        }
    } elseif ($postAction === 'edit' && isset($_POST['user_id'])) {
        try {
            $userId = $_POST['user_id'];
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $isPremium = isset($_POST['is_premium']) ? 1 : 0;
            
            // Validate required fields
            if (empty($username)) {
                throw new Exception('Username is required');
            }
            if (empty($email)) {
                throw new Exception('Email is required');
            }
            
            $data = [
                'username' => $username,
                'email' => $email,
                'is_premium' => $isPremium
            ];
            
            // Only include password if provided
            if (!empty($password)) {
                $data['password'] = $password;
            }
            
            $result = $user->updateUser($userId, $data);
            
            if ($result['success']) {
                header('Location: users.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error updating user: ' . $e->getMessage();
        }
    } elseif ($postAction === 'delete' && isset($_POST['user_id'])) {
        try {
            $userId = $_POST['user_id'];
            
            $result = $user->deleteUser($userId);
            
            if ($result['success']) {
                header('Location: users.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error deleting user: ' . $e->getMessage();
        }
    }
}

// Set page variables for head component
$pageTitle = $action === 'add' ? 'Add New User' : ($action === 'edit' ? 'Edit User' : 'Manage Users');
$pageDescription = 'Manage blog users and permissions';

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

            <?php if ($action === 'list'): ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Users</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="users.php?action=add" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>New User
                    </a>
                </div>
            </div>

            <!-- Users List -->
            <div class="row">
                <div class="col-md-8">
                    <?php
                    $allUsers = $user->getAllUsers();
                    ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Posts</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allUsers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <p>No users found.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($allUsers as $userItem): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($userItem['username']); ?></strong>
                                                <?php if ($userItem['id'] == $currentUser['id']): ?>
                                                    <span class="badge bg-info ms-1">You</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($userItem['email']); ?></td>
                                    <td>
                                        <?php if ($userItem['is_premium']): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-crown me-1"></i>Premium
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo $userItem['post_count']; ?></span></td>
                                    <td><?php echo date('Y-m-d', strtotime($userItem['created_at'])); ?></td>
                                    <td>
                                        <a href="users.php?action=edit&id=<?php echo $userItem['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php if ($userItem['id'] != $currentUser['id']): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $userItem['id']; ?>, '<?php echo htmlspecialchars($userItem['username']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                        <?php endif; ?>
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
                            <h6 class="mb-0">User Statistics</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $stats = $user->getUserStatistics();
                            ?>
                            <div class="mb-3">
                                <label class="form-label">Total Users</label>
                                <h4 class="text-primary mb-0"><?php echo $stats['total_users'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Premium Users</label>
                                <h4 class="text-warning mb-0"><?php echo $stats['premium_users'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Recent Registration</label>
                                <p class="text-muted mb-0"><?php echo $stats['recent_registration'] ?? 'No users'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($action === 'add'): ?>
            <!-- Add User Form -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New User</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="users.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
            </div>

            <form method="POST" action="users.php?action=add">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="form-text">Choose a unique username for the user.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="form-text">Enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Choose a strong password for the user.</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">User Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium">
                                        <label class="form-check-label" for="is_premium">Premium User</label>
                                    </div>
                                    <div class="form-text">Premium users have access to admin features.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create User
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
            <!-- Edit User Form -->
            <?php
            $userId = $_GET['id'];
            $userData = $user->getUserById($userId);
            
            if (!$userData) {
                echo '<div class="alert alert-danger">User not found.</div>';
            } else {
            ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit User</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="users.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
            </div>

            <form method="POST" action="users.php?action=edit">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                            <div class="form-text">Choose a unique username for the user.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                            <div class="form-text">Enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                            <div class="form-text">Leave blank to keep the current password unchanged.</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">User Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" <?php echo ($userData['is_premium']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_premium">Premium User</label>
                                    </div>
                                    <div class="form-text">Premium users have access to admin features.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update User
                            </button>
                            <a href="users.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            <?php } ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword.value) {
        if (this.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
});

// Delete user function
function deleteUser(userId, username) {
    if (confirm('Are you sure you want to delete the user "' + username + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'users.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        form.appendChild(actionInput);
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?> 