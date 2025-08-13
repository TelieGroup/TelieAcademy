<!-- Admin Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>" href="posts.php">
                    <i class="fas fa-file-alt me-2"></i>Posts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                    <i class="fas fa-folder me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tags.php' ? 'active' : ''; ?>" href="tags.php">
                    <i class="fas fa-tags me-2"></i>Tags
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users me-2"></i>Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>" href="comments.php">
                    <i class="fas fa-comments me-2"></i>Comments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'newsletter.php' ? 'active' : ''; ?>" href="newsletter.php">
                    <i class="fas fa-envelope me-2"></i>Newsletter
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'send_newsletter.php' ? 'active' : ''; ?>" href="send_newsletter.php">
                    <i class="fas fa-paper-plane me-2"></i>Send Newsletter
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>" href="media.php">
                    <i class="fas fa-images me-2"></i>Media
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>" href="courses.php">
                    <i class="fas fa-graduation-cap me-2"></i>Course Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'download_analytics.php' ? 'active' : ''; ?>" href="download_analytics.php">
                    <i class="fas fa-chart-line me-2"></i>Download Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact_messages.php' ? 'active' : ''; ?>" href="contact_messages.php">
                    <i class="fas fa-envelope me-2"></i>Contact Messages
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
            </li>
        </ul>
    </div>
</nav> 