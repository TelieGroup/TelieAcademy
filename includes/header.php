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
<nav class="navbar navbar-expand-lg navbar-light bg-primary fixed-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../index' : 'index'; ?>">
            <i class="fas fa-graduation-cap me-2"></i>TelieAcademy
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Main Navigation Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../index' : 'index'; ?>">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../categories' : 'categories'; ?>">
                        <i class="fas fa-folder me-1"></i>Disciplines
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>" href="<?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? '#' : (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../posts' : 'posts'); ?>">
                        <i class="fas fa-book me-1"></i>Materials
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tags.php' ? 'active' : ''; ?>" href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../tags' : 'tags'; ?>">
                        <i class="fas fa-tags me-1"></i>Topics
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (basename($_SERVER['PHP_SELF']) == 'courses.php' || basename($_SERVER['PHP_SELF']) == 'course-view.php') ? 'active' : ''; ?>" href="#" id="coursesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-graduation-cap me-1"></i>Programs
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="coursesDropdown">
                        <li><a class="dropdown-item" href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../courses' : 'courses'; ?>">
                            <i class="fas fa-list me-2"></i>All Programs
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../courses' : 'courses'; ?>">
                            <i class="fas fa-download me-2"></i>Materials
                        </a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact-us.php' ? 'active' : ''; ?>" href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../contact-us' : 'contact-us'; ?>">
                        <i class="fas fa-envelope me-1"></i>Support
                    </a>
                </li>

            </ul>
            
            <!-- User Actions Section -->
            <div class="navbar-nav align-items-center">
                <?php if ($isLoggedIn): ?>
                    <?php
                    // Compute unread contact replies badge
                    $unreadContactReplies = 0;
                    try {
                        require_once dirname(__DIR__) . '/includes/ContactMessage.php';
                        $cmForHeader = new ContactMessage();
                        $unreadContactReplies = $cmForHeader->getUnreadReplyCountForUser($currentUser['id']);
                    } catch (Exception $e) {
                        $unreadContactReplies = 0;
                    }
                    ?>
                <?php endif; ?>
                
                <!-- Newsletter Subscription -->
                <div class="nav-item dropdown me-2">
                    <?php if ($userSubscription && $userSubscription['is_active']): ?>
                        <a class="nav-link dropdown-toggle text-dark" href="#" id="newsletterDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-envelope"></i>
                            <span class="badge bg-<?php echo $userSubscription['subscription_type'] === 'premium' ? 'warning' : 'success'; ?> ms-1">
                                <?php echo ucfirst($userSubscription['subscription_type']); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">Membership Status</h6></li>
                            <li><span class="dropdown-item-text">
                                <small>
                                    Type: <strong><?php echo ucfirst($userSubscription['subscription_type']); ?></strong><br>
                                    Frequency: <strong><?php echo ucfirst($userSubscription['frequency']); ?></strong>
                                    <?php if ($userSubscription['subscription_type'] === 'premium' && $userSubscription['premium_expires_at']): ?>
                                        <br>Expires: <strong><?php echo date('M j, Y', strtotime($userSubscription['premium_expires_at'])); ?></strong>
                                    <?php endif; ?>
                                </small>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="subscription-settings">
                                <i class="fas fa-cog me-2"></i>Manage Membership
                            </a></li>
                            <li><a class="dropdown-item text-warning" href="unsubscribe">
                                <i class="fas fa-unlink me-2"></i>Cancel
                            </a></li>
                        </ul>
                    <?php else: ?>
                        <a class="nav-link text-dark" href="#" data-bs-toggle="modal" data-bs-target="#newsletterModal">
                            <i class="fas fa-envelope"></i>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Dark Mode Toggle -->
                <div class="nav-item me-2">
                    <button class="btn btn-light btn-sm" id="darkModeToggle" title="Toggle Dark Mode">
                        <i class="fas fa-moon text-primary"></i>
                    </button>
                </div>
                
                <!-- User Authentication -->
                <div class="nav-item">
                    <?php if (!$isLoggedIn): ?>
                        <!-- Login Button for non-logged in users -->
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-user me-1 text-primary"></i><span class="text-primary">Login</span>
                        </button>
                    <?php else: ?>
                        <!-- User Info for logged in users -->
                        <div class="d-flex align-items-center">
                            <!-- Messages Button -->
                            <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../my-messages' : 'my-messages'; ?>" class="btn btn-light btn-sm me-2 position-relative" title="Messages">
                                <i class="fas fa-inbox text-primary"></i>
                                <?php if (!empty($unreadContactReplies)): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo (int)$unreadContactReplies; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                            
                            <!-- User Profile Section -->
                            <div class="d-flex align-items-center me-2">
                                <!-- Profile Picture -->
                                <div class="me-2">
                                    <?php if (!empty($currentUser['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($currentUser['profile_picture']); ?>" 
                                             alt="Profile" 
                                             class="rounded-circle" 
                                             style="width: 32px; height: 32px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                                             loading="lazy" decoding="async" referrerpolicy="no-referrer"
                                             onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=&#39;http://www.w3.org/2000/svg&#39; width=&#39;120&#39; height=&#39;120&#39; viewBox=&#39;0 0 120 120&#39;><rect width=&#39;100%&#39; height=&#39;100%&#39; fill=&#39;%23adb5bd&#39;/><circle cx=&#39;60&#39; cy=&#39;45&#39; r=&#39;25&#39; fill=&#39;%23dee2e6&#39;/><rect x=&#39;20&#39; y=&#39;80&#39; width=&#39;80&#39; height=&#39;25&#39; rx=&#39;12&#39; fill=&#39;%23dee2e6&#39;/></svg>';">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle text-dark" style="font-size: 32px; text-shadow: 0 1px 2px rgba(255,255,255,0.3);"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Username (Desktop Only) -->
                                <div class="d-none d-lg-block">
                                    <small class="text-dark fw-semibold" style="font-size: 0.9rem; text-shadow: 0 1px 2px rgba(255,255,255,0.3);">
                                        <?php echo htmlspecialchars($currentUser['username']); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-1">
                                <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../bookmarks' : 'bookmarks'; ?>" class="btn btn-light btn-sm" title="Bookmarks">
                                    <i class="fas fa-bookmark text-primary"></i>
                                </a>
                                <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../profile' : 'profile'; ?>" class="btn btn-light btn-sm" title="Profile">
                                    <i class="fas fa-user text-primary"></i>
                                </a>
                                <?php if ($isAdmin): ?>
                                <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'index' : 'admin/'; ?>" class="btn btn-warning btn-sm" title="Admin">
                                    <i class="fas fa-cog text-white"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../logout' : 'logout'; ?>" class="btn btn-danger btn-sm" title="Logout">
                                    <i class="fas fa-sign-out-alt text-white"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav> 