<?php
// Check if user is logged in and is admin
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/includes/User.php';
require_once dirname(__DIR__) . '/includes/Newsletter.php';

$user = new User();
$newsletter = new Newsletter();
$isLoggedIn = $user->isLoggedIn();
$isAdmin = false;
$userSubscription = null;

if ($isLoggedIn) {
    $currentUser = $user->getCurrentUser();
    $isAdmin = $currentUser && $currentUser['is_admin'];
    
    // Get user's newsletter subscription
            $userSubscription = $newsletter->getUserSubscription($currentUser['id'], $currentUser['email']);
}
?>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-graduation-cap me-2"></i>TelieAcademy
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tags.php' ? 'active' : ''; ?>" href="tags.php">Tags</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>" href="posts.php">All Posts</a>
                </li>
                <li class="nav-item dropdown">
                    <?php if ($userSubscription && $userSubscription['is_active']): ?>
                        <a class="nav-link dropdown-toggle" href="#" id="newsletterDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-envelope"></i> Newsletter 
                            <span class="badge bg-<?php echo $userSubscription['subscription_type'] === 'premium' ? 'warning' : 'success'; ?> ms-1">
                                <?php echo ucfirst($userSubscription['subscription_type']); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">Subscription Status</h6></li>
                            <li><span class="dropdown-item-text">
                                <small>
                                    Type: <strong><?php echo ucfirst($userSubscription['subscription_type']); ?></strong><br>
                                    Frequency: <strong><?php echo ucfirst($userSubscription['frequency']); ?></strong><br>
                                    <?php if ($userSubscription['subscription_type'] === 'premium' && $userSubscription['premium_expires_at']): ?>
                                        Expires: <strong><?php echo date('M j, Y', strtotime($userSubscription['premium_expires_at'])); ?></strong>
                                    <?php endif; ?>
                                </small>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="subscription-settings.php">
                                <i class="fas fa-cog me-2"></i>Manage Subscription
                            </a></li>
                            <li><a class="dropdown-item text-warning" href="unsubscribe.php">
                                <i class="fas fa-unlink me-2"></i>Unsubscribe
                            </a></li>
                        </ul>
                    <?php else: ?>
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#newsletterModal">
                            <i class="fas fa-envelope"></i> Newsletter
                        </a>
                    <?php endif; ?>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <span class="text-white" id="loginBtnSpan" style="display: none;">
                <button class="btn btn-outline-primary me-2" id="loginBtn" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fas fa-user"></i> Login
                </button>
                </span>
                <span id="userInfo" class="text-white" style="display: none;">
                    <span style="color:#007bff">Welcome,</span> <span id="username"></span>!
                    <a href="profile.php" class="btn btn-outline-info btn-sm ms-2" id="profileBtn">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <?php if ($isAdmin): ?>
                    <a href="admin/" class="btn btn-outline-warning btn-sm ms-2" id="adminBtn">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                    <?php endif; ?>
                    <button class="btn btn-outline-primary btn-sm ms-2" id="logoutBtn">Logout</button>
                </span>
                <button class="btn btn-outline-primary me-2 ms-2" id="darkModeToggle">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>
</nav> 