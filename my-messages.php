<?php
require_once 'config/session.php';
require_once 'includes/User.php';
require_once 'includes/ContactMessage.php';

$user = new User();
if (!$user->isLoggedIn()) {
    header('Location: index');
    exit();
}

$currentUser = $user->getCurrentUser();
$contactMessage = new ContactMessage();

// Clear unread replies badge: mark user's messages as viewed upon opening this page
$contactMessage->markAllAsViewedByUser($currentUser['id']);

// Get user's messages
$userMessages = $contactMessage->getMessagesByUser($currentUser['id']);

$pageTitle = "My Messages - TelieAcademy";
$pageDescription = "View your contact messages and admin replies";

include 'includes/head.php';
?>

<style>
.messages-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

.message-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.message-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.message-subject {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.message-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.875rem;
    color: #6c757d;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
}

.status-new {
    background: #007bff;
    color: white;
}

.status-in_progress {
    background: #fd7e14;
    color: white;
}

.status-replied {
    background: #28a745;
    color: white;
}

.status-closed {
    background: #6c757d;
    color: white;
}

.priority-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
}

.priority-urgent {
    background: #dc3545;
    color: white;
}

.priority-high {
    background: #fd7e14;
    color: white;
}

.priority-medium {
    background: #ffc107;
    color: #212529;
}

.priority-low {
    background: #28a745;
    color: white;
}

.message-content {
    margin-bottom: 1.5rem;
    line-height: 1.6;
    color: #2c3e50;
}

.replies-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.reply-item {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 4px solid #007bff;
}

.reply-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.reply-author {
    font-weight: 600;
    color: #007bff;
}

.reply-date {
    font-size: 0.875rem;
    color: #6c757d;
}

.reply-content {
    color: #2c3e50;
    line-height: 1.5;
}

.no-messages {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.no-messages i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

.page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-header h1 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 1rem;
}

.page-header p {
    color: #6c757d;
    font-size: 1.1rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    color: #007bff;
    text-decoration: none;
    margin-bottom: 2rem;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #0056b3;
    text-decoration: underline;
}

.back-link i {
    margin-right: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .message-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .message-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .messages-container {
        padding: 1rem 0;
    }
}

/* Dark Mode Styles for My Messages Page */
.dark-mode .messages-container {
    background: var(--background-color) !important;
}

.dark-mode .message-card {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
    box-shadow: 0 2px 10px var(--shadow-color-dark) !important;
}

.dark-mode .message-card:hover {
    box-shadow: 0 4px 20px var(--shadow-color-dark) !important;
}

.dark-mode .message-header {
    border-bottom-color: var(--border-color) !important;
}

.dark-mode .message-subject {
    color: var(--text-color) !important;
}

.dark-mode .message-meta {
    color: var(--text-muted) !important;
}

.dark-mode .message-content {
    color: var(--text-color) !important;
}

.dark-mode .replies-section {
    background: var(--background-color) !important;
}

.dark-mode .reply-item {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.dark-mode .reply-content {
    color: var(--text-color) !important;
}

.dark-mode .reply-date {
    color: var(--text-muted) !important;
}

.dark-mode .no-messages {
    color: var(--text-muted) !important;
}

.dark-mode .no-messages i {
    color: var(--border-color) !important;
}

.dark-mode .page-header h1 {
    color: var(--text-color) !important;
}

.dark-mode .page-header p {
    color: var(--text-muted) !important;
}

.dark-mode .back-link {
    color: #007bff !important;
}

.dark-mode .back-link:hover {
    color: #0056b3 !important;
}

.dark-mode .text-muted {
    color: var(--text-muted) !important;
}

/* Dark Mode Badge Styles */
.dark-mode .status-badge {
    color: white !important;
}

.dark-mode .priority-badge {
    color: white !important;
}

.dark-mode .priority-medium {
    color: #212529 !important;
}

.dark-mode .badge {
    color: white !important;
}

.dark-mode .badge.bg-info {
    background-color: #17a2b8 !important;
}

/* Dark Mode Reply Author */
.dark-mode .reply-author {
    color: #007bff !important;
}

/* Dark Mode Small Text */
.dark-mode small {
    color: var(--text-muted) !important;
}
</style>

<?php include 'includes/header.php'; ?>

<div class="messages-container">
    <div class="container">
        <!-- Back Link -->
                    <a href="contact-us" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Contact Form
        </a>
        
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-envelope me-2"></i>My Messages</h1>
            <p>View your contact messages and track responses from our team</p>
        </div>
        
        <!-- Messages List -->
        <?php if (empty($userMessages)): ?>
            <div class="no-messages">
                <i class="fas fa-inbox"></i>
                <h3>No messages yet</h3>
                <p>You haven't sent any contact messages yet.</p>
                <a href="contact-us" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Send Your First Message
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($userMessages as $message): ?>
                <div class="message-card">
                    <div class="message-header">
                        <div>
                            <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                            <div class="message-meta">
                                <span><i class="fas fa-calendar me-1"></i><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></span>
                                <span class="status-badge status-<?php echo $message['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $message['status'])); ?>
                                </span>
                                <span class="priority-badge priority-<?php echo $message['priority']; ?>">
                                    <?php echo ucfirst($message['priority']); ?>
                                </span>
                                <?php if ($message['reply_count'] > 0): ?>
                                    <span class="badge bg-info">
                                        <?php echo $message['reply_count']; ?> Reply<?php echo $message['reply_count'] !== 1 ? 'ies' : ''; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                    
                    <?php if ($message['reply_count'] > 0): ?>
                        <div class="replies-section">
                            <h5><i class="fas fa-reply me-2"></i>Admin Replies</h5>
                            <?php 
                            // Get full message details including replies
                            $fullMessage = $contactMessage->getMessageById($message['id']);
                            if ($fullMessage && !empty($fullMessage['replies'])):
                                foreach ($fullMessage['replies'] as $reply):
                                    // Only show non-internal replies to users
                                    if (!$reply['is_internal']):
                            ?>
                                <div class="reply-item">
                                    <div class="reply-header">
                                        <span class="reply-author">
                                            <i class="fas fa-user-shield me-1"></i>
                                            <?php echo htmlspecialchars($reply['admin_username']); ?>
                                        </span>
                                        <span class="reply-date">
                                            <?php echo date('M j, Y g:i A', strtotime($reply['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="reply-content">
                                        <?php echo nl2br(htmlspecialchars($reply['reply_message'])); ?>
                                    </div>
                                </div>
                            <?php 
                                    endif;
                                endforeach;
                            endif;
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($message['status'] === 'new'): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                We'll respond to your message within 24-48 hours.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

