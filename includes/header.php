<?php
// Check if user is logged in and is admin
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/includes/User.php';

$user = new User();
$isLoggedIn = $user->isLoggedIn();
$isAdmin = false;

if ($isLoggedIn) {
    $currentUser = $user->getCurrentUser();
    $isAdmin = $currentUser && $currentUser['is_premium'];
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
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#newsletterModal">Newsletter</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <span class="text-white" id="loginBtnSpan" style="display: none;">
                <button class="btn btn-outline-primary me-2" id="loginBtn" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fas fa-user"></i> Login
                </button>
                </span>
                <span id="userInfo" class="text-white" style="display: none;">
                    Welcome, <span id="username"></span>!
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