<!-- Admin Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>" href="posts">
                    <i class="fas fa-file-alt me-2"></i>Posts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories">
                    <i class="fas fa-folder me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tags.php' ? 'active' : ''; ?>" href="tags">
                    <i class="fas fa-tags me-2"></i>Tags
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users">
                    <i class="fas fa-users me-2"></i>Users
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/includes/User.php';
                        $userForBadge = new User();
                        $newUsersToday = $userForBadge->getNewUsersToday();
                        if ($newUsersToday > 0):
                        ?>
                        <span class="badge bg-success ms-1"><?php echo $newUsersToday; ?> New</span>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>" href="comments">
                    <i class="fas fa-comments me-2"></i>Comments
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/includes/Comment.php';
                        $commentForBadge = new Comment();
                        $pendingCommentCount = $commentForBadge->getPendingCommentCount();
                        if ($pendingCommentCount > 0):
                        ?>
                        <span class="badge bg-warning text-dark ms-1"><?php echo $pendingCommentCount; ?></span>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'newsletter.php' ? 'active' : ''; ?>" href="newsletter">
                    <i class="fas fa-envelope me-2"></i>Newsletter
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/includes/Newsletter.php';
                        $newsletterForBadge = new Newsletter();
                        $pendingSubscriptionCount = $newsletterForBadge->getPendingSubscriptionCount();
                        $unviewedFeedbackCount = $newsletterForBadge->getUnviewedUnsubscribeFeedbackCount();
                        $totalCount = $pendingSubscriptionCount + $unviewedFeedbackCount;
                        if ($totalCount > 0):
                        ?>
                        <span class="badge bg-warning text-dark ms-1"><?php echo $totalCount; ?></span>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'send_newsletter.php' ? 'active' : ''; ?>" href="send_newsletter">
                    <i class="fas fa-paper-plane me-2"></i>Send Newsletter
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/includes/Newsletter.php';
                        $newsletterForSendBadge = new Newsletter();
                        $activeSubscriberCount = $newsletterForSendBadge->getTotalSubscriberCount();
                        if ($activeSubscriberCount > 0):
                        ?>
                        <span class="badge bg-info ms-1"><?php echo $activeSubscriberCount; ?></span>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>" href="media">
                    <i class="fas fa-images me-2"></i>Media
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>" href="courses">
                    <i class="fas fa-graduation-cap me-2"></i>Course Management
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/includes/Course.php';
                        $courseForCourseBadge = new Course();
                        $courseStats = $courseForCourseBadge->getCourseStatistics();
                        $totalCourses = $courseStats['total_courses'] ?? 0;
                        if ($totalCourses > 0):
                        ?>
                        <span class="badge bg-info ms-1"><?php echo $totalCourses; ?> Courses</span>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'download_analytics.php' ? 'active' : ''; ?>" href="download_analytics">
                    <i class="fas fa-chart-line me-2"></i>Download Analytics
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/includes/Course.php';
                        $courseForBadge = new Course();
                        $downloadStats = $courseForBadge->getDownloadStatistics();
                        $downloadsToday = $downloadStats['downloads_today'] ?? 0;
                        if ($downloadsToday > 0):
                        ?>
                        <span class="badge bg-success ms-1"><?php echo $downloadsToday; ?> Today</span>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact_messages.php' ? 'active' : ''; ?>" href="contact_messages">
                    <i class="fas fa-envelope me-2"></i>Contact Messages
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/includes/ContactMessage.php';
                        $contactMessageForBadge = new ContactMessage();
                        $newMessageCount = $contactMessageForBadge->getNewMessageCount();
                        if ($newMessageCount > 0):
                        ?>
                        <span class="badge bg-danger ms-1"><?php echo $newMessageCount; ?></span>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
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