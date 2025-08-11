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

// Check for authentication messages
$authMessage = $_SESSION['auth_message'] ?? '';
$authMessageType = $_SESSION['auth_message_type'] ?? '';
$authError = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_message'], $_SESSION['auth_message_type'], $_SESSION['auth_error']);

// Combine auth messages
if (!empty($authError)) {
    $authMessage = $authError;
    $authMessageType = 'error';
}
?>

<!-- Authentication Message -->
<?php if (!empty($authMessage)): ?>
<div class="alert alert-<?php echo $authMessageType === 'error' ? 'danger' : $authMessageType; ?> alert-dismissible fade show" role="alert" style="margin-top: 76px;">
    <i class="fas fa-<?php echo $authMessageType === 'success' ? 'check-circle' : ($authMessageType === 'error' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
    <?php echo htmlspecialchars($authMessage); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

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
            <!-- Main Navigation Links -->
            <div class="navbar-nav me-auto mb-2 mb-lg-0">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">Categories</a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>" href="posts.php">All Posts</a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tags.php' ? 'active' : ''; ?>" href="tags.php">Tags</a>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>" href="search.php">
                    <i class="fas fa-search me-1"></i>Search
                </a>
            </div>
            
            <!-- Search Bar - Responsive Design -->
            <form class="d-flex me-2 me-lg-3" role="search" id="searchForm">
                <div class="input-group input-group-sm">
                    <input class="form-control" type="search" placeholder="Search..." aria-label="Search" id="searchInput">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- User Actions Section -->
            <div class="navbar-nav align-items-center">
                <!-- Newsletter Subscription -->
                <div class="nav-item dropdown me-2">
                    <?php if ($userSubscription && $userSubscription['is_active']): ?>
                        <a class="nav-link dropdown-toggle" href="#" id="newsletterDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-envelope"></i>
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
                            <i class="fas fa-envelope"></i>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Dark Mode Toggle -->
                <div class="nav-item me-2">
                    <button class="btn btn-outline-primary btn-sm" id="darkModeToggle" title="Toggle Dark Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
                
                <!-- User Authentication -->
                <div class="nav-item">
                    <span class="text-white" id="loginBtnSpan" style="display: none;">
                        <button class="btn btn-outline-primary btn-sm" id="loginBtn" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-user"></i> Login
                        </button>
                    </span>
                    <span id="userInfo" class="text-white" style="display: none;">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <?php if ($isLoggedIn && !empty($currentUser['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($currentUser['profile_picture']); ?>" 
                                         alt="Profile Picture" 
                                         class="rounded-circle" 
                                         style="width: 28px; height: 28px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user-circle text-light"></i>
                                <?php endif; ?>
                            </div>
                            <div class="d-none d-lg-block me-2">
                                <small>
                                    <span id="username"></span>!
                                </small>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-1">
                                <a href="bookmarks.php" class="btn btn-outline-primary btn-sm" id="bookmarksBtn" title="My Bookmarks">
                                    <i class="fas fa-bookmark"></i>
                                </a>
                                <a href="profile.php" class="btn btn-outline-primary btn-sm" id="profileBtn" title="Profile">
                                    <i class="fas fa-user"></i>
                                </a>
                                <?php if ($isAdmin): ?>
                                <a href="admin/" class="btn btn-outline-warning btn-sm" id="adminBtn" title="Admin Panel">
                                    <i class="fas fa-cog"></i>
                                </a>
                                <?php endif; ?>
                                <button class="btn btn-outline-primary btn-sm" id="logoutBtn" title="Logout">Logout</button>
                            </div>
                        </div>
                    </span>
                </div>
            </div>
        </div>
    </div>
</nav> 