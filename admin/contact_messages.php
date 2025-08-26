<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/ContactMessage.php';

// Check if user is admin
$user = new User();
if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header('Location: ../index');
    exit();
}

$currentUser = $user->getCurrentUser();
$contactMessage = new ContactMessage();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_reply':
            $messageId = $_POST['message_id'] ?? '';
            $replyMessage = $_POST['reply_message'] ?? '';
            $isInternal = isset($_POST['is_internal']) ? 1 : 0;
            
            if ($contactMessage->addReply($messageId, $currentUser['id'], $replyMessage, $isInternal)) {
                $successMessage = "Reply added successfully!";
            } else {
                $errorMessage = "Failed to add reply. Please try again.";
            }
            break;
            
        case 'update_status':
            $messageId = $_POST['message_id'] ?? '';
            $status = $_POST['status'] ?? '';
            $adminNotes = $_POST['admin_notes'] ?? '';
            
            if ($contactMessage->updateMessageStatus($messageId, $status, $currentUser['id'], $adminNotes)) {
                $successMessage = "Message status updated successfully!";
            } else {
                $errorMessage = "Failed to update message status. Please try again.";
            }
            break;
            
        case 'update_priority':
            $messageId = $_POST['message_id'] ?? '';
            $priority = $_POST['priority'] ?? '';
            
            if ($contactMessage->updateMessagePriority($messageId, $priority)) {
                $successMessage = "Message priority updated successfully!";
            } else {
                $errorMessage = "Failed to update message priority. Please try again.";
            }
            break;
            
        case 'delete_message':
            $messageId = $_POST['message_id'] ?? '';
            
            if ($contactMessage->deleteMessage($messageId)) {
                $successMessage = "Message deleted successfully!";
            } else {
                $errorMessage = "Failed to delete message. Please try again.";
            }
            break;
    }
}

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'subject' => $_GET['subject'] ?? '',
    'email' => $_GET['email'] ?? ''
];

$page = $_GET['page'] ?? 1;
$messages = $contactMessage->getAllMessages($filters, $page, 20);
$statistics = $contactMessage->getMessageStatistics();

$pageTitle = "Contact Messages - Admin";
include '../includes/head.php';
include '../includes/header.php';
?>

<style>
.contact-messages-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 6rem 0;
}

.stats-cards {
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.urgent {
    border-left: 4px solid #dc3545;
}

.stat-card.high {
    border-left: 4px solid #fd7e14;
}

.stat-card.medium {
    border-left: 4px solid #ffc107;
}

.stat-card.low {
    border-left: 4px solid #28a745;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.message-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
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
}

.message-info {
    flex: 1;
}

.message-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
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

.message-content {
    margin-bottom: 1rem;
}

.message-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.875rem;
    color: #6c757d;
}

.reply-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.reply-form {
    margin-top: 1rem;
}

.reply-form textarea {
    min-height: 100px;
}

.internal-note {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 0.75rem;
    margin-top: 0.5rem;
    font-size: 0.875rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .message-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .message-actions {
        justify-content: flex-start;
    }
    
    .message-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .stats-cards .col-md-3 {
        margin-bottom: 1rem;
    }
}

/* Dark Mode Support */
.dark-mode .contact-messages-container {
    background: #1a1a1a;
}

.dark-mode .stat-card {
    background: #2d2d2d;
    color: #e0e0e0;
    border: 1px solid #404040;
}

.dark-mode .stat-card:hover {
    background: #353535;
    border-color: #505050;
}

.dark-mode .stat-label {
    color: #b0b0b0;
}

.dark-mode .filters-section {
    background: #2d2d2d;
    color: #e0e0e0;
    border: 1px solid #404040;
}

.dark-mode .filters-section label {
    color: #e0e0e0;
}

.dark-mode .filters-section .form-control,
.dark-mode .filters-section .form-select {
    background: #404040;
    border-color: #505050;
    color: #e0e0e0;
}

.dark-mode .filters-section .form-control:focus,
.dark-mode .filters-section .form-select:focus {
    background: #404040;
    border-color: #007bff;
    color: #e0e0e0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.dark-mode .message-card {
    background: #2d2d2d;
    color: #e0e0e0;
    border: 1px solid #404040;
}

.dark-mode .message-card:hover {
    background: #353535;
    border-color: #505050;
}

.dark-mode .message-content h6 {
    color: #ffffff;
}

.dark-mode .message-content p {
    color: #d0d0d0;
}

.dark-mode .message-meta {
    color: #b0b0b0;
}

.dark-mode .internal-note {
    background: #3d2e1a;
    border-color: #5a4a2a;
    color: #e0e0e0;
}

.dark-mode .reply-section {
    background: #1a1a1a;
}

.dark-mode .reply-form textarea {
    background: #404040;
    border-color: #505050;
    color: #e0e0e0;
}

.dark-mode .reply-form textarea:focus {
    background: #404040;
    border-color: #007bff;
    color: #e0e0e0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.dark-mode .modal-content {
    background: #2d2d2d;
    color: #e0e0e0;
    border: 1px solid #404040;
}

.dark-mode .modal-header {
    background: #353535;
    border-bottom-color: #404040;
}

.dark-mode .modal-footer {
    background: #353535;
    border-top-color: #404040;
}

.dark-mode .dropdown-menu {
    background: #2d2d2d;
    border: 1px solid #404040;
}

.dark-mode .dropdown-item {
    color: #e0e0e0;
}

.dark-mode .dropdown-item:hover {
    background: #404040;
    color: #ffffff;
}

.dark-mode .dropdown-header {
    color: #b0b0b0;
}
</style>

<?php include '../includes/header.php'; ?>

<!-- Admin CSS -->
<link rel="stylesheet" href="admin.css">

<div class="contact-messages-container">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/admin_sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="fas fa-envelope me-2"></i>Contact Messages
                <?php
                try {
                    $newMessageCount = $contactMessage->getNewMessageCount();
                    if ($newMessageCount > 0):
                    ?>
                    <span class="badge bg-danger ms-2"><?php echo $newMessageCount; ?> New</span>
                    <?php endif; ?>
                <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </h1>
            <div>
                <a href="index" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card urgent">
                        <div class="stat-number"><?php echo $statistics['urgent_messages'] ?? 0; ?></div>
                        <div class="stat-label">Urgent Messages</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card high">
                        <div class="stat-number"><?php echo $statistics['high_priority_messages'] ?? 0; ?></div>
                        <div class="stat-label">High Priority</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card medium">
                        <div class="stat-number"><?php echo $statistics['new_messages'] ?? 0; ?></div>
                        <div class="stat-label">New Messages</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card low">
                        <div class="stat-number"><?php echo $statistics['total_messages'] ?? 0; ?></div>
                        <div class="stat-label">Total Messages</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="new" <?php echo $filters['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="in_progress" <?php echo $filters['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="replied" <?php echo $filters['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                        <option value="closed" <?php echo $filters['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="urgent" <?php echo $filters['priority'] === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        <option value="high" <?php echo $filters['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $filters['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $filters['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" 
                           value="<?php echo htmlspecialchars($filters['subject'] ?? ''); ?>" 
                           placeholder="Search by subject...">
                </div>
                <div class="col-md-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           value="<?php echo htmlspecialchars($filters['email'] ?? ''); ?>" 
                           placeholder="Search by email...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="contact_messages" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Messages List -->
        <div class="messages-list">
            <?php if (empty($messages)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4>No messages found</h4>
                    <p class="text-muted">No contact messages match your current filters.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div class="message-info">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <?php if (!empty($message['user_profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($message['user_profile_picture']); ?>" 
                                             alt="User Avatar" class="user-avatar">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <h5 class="mb-1">
                                            <?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?>
                                            <?php if ($message['user_username']): ?>
                                                <small class="text-muted">(@<?php echo htmlspecialchars($message['user_username']); ?>)</small>
                                            <?php endif; ?>
                                        </h5>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="priority-badge priority-<?php echo $message['priority']; ?>">
                                                <?php echo ucfirst($message['priority']); ?>
                                            </span>
                                            <span class="status-badge status-<?php echo $message['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $message['status'])); ?>
                                            </span>
                                            <?php if ($message['reply_count'] > 0): ?>
                                                <span class="badge bg-info">
                                                    <?php echo $message['reply_count']; ?> Reply<?php echo $message['reply_count'] !== 1 ? 'ies' : ''; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="message-actions">
                                <button class="btn btn-primary btn-sm" onclick="viewMessage(<?php echo $message['id']; ?>)">
                                    <i class="fas fa-eye me-1"></i>View
                                </button>
                                <button class="btn btn-success btn-sm" onclick="replyToMessage(<?php echo $message['id']; ?>)">
                                    <i class="fas fa-reply me-1"></i>Reply
                                </button>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog me-1"></i>Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $message['id']; ?>, 'in_progress')">
                                            <i class="fas fa-clock me-2"></i>Mark In Progress
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $message['id']; ?>, 'closed')">
                                            <i class="fas fa-check me-2"></i>Mark Closed
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteMessage(<?php echo $message['id']; ?>)">
                                            <i class="fas fa-trash me-2"></i>Delete
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="message-content">
                            <h6 class="mb-2"><?php echo htmlspecialchars($message['subject']); ?></h6>
                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                        </div>
                        
                        <div class="message-meta">
                            <span><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($message['email']); ?></span>
                            <?php if ($message['phone']): ?>
                                <span><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($message['phone']); ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-calendar me-1"></i><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></span>
                            <?php if ($message['newsletter_subscribe']): ?>
                                <span class="badge bg-success"><i class="fas fa-envelope me-1"></i>Newsletter</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($message['admin_notes']): ?>
                            <div class="internal-note">
                                <strong>Admin Notes:</strong> <?php echo nl2br(htmlspecialchars($message['admin_notes'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
            </main>
        </div>
    </div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewMessageContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reply to Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="replyForm" method="POST">
                    <input type="hidden" name="action" value="add_reply">
                    <input type="hidden" name="message_id" id="replyMessageId">
                    
                    <div class="mb-3">
                        <label for="reply_message" class="form-label">Reply Message *</label>
                        <textarea class="form-control" id="reply_message" name="reply_message" rows="6" required 
                                  placeholder="Type your reply here..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_internal" name="is_internal">
                            <label class="form-check-label" for="is_internal">
                                This is an internal note (not visible to the user)
                            </label>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Message Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm" method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="message_id" id="updateStatusMessageId">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="new">New</option>
                            <option value="in_progress">In Progress</option>
                            <option value="replied">Replied</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" 
                                  placeholder="Add internal notes about this message..."></textarea>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// View message details
function viewMessage(messageId) {
    fetch(`get_message?id=${messageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const message = data.message;
                let content = `
                    <div class="message-details">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>From:</strong> ${message.first_name} ${message.last_name}<br>
                                <strong>Email:</strong> ${message.email}<br>
                                ${message.phone ? `<strong>Phone:</strong> ${message.phone}<br>` : ''}
                                <strong>Subject:</strong> ${message.subject}
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="priority-badge priority-${message.priority}">${message.priority}</span>
                                <span class="status-badge status-${message.status}">${message.status.replace('_', ' ')}</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Message:</strong><br>
                            <div class="border rounded p-3 bg-light">${message.message.replace(/\n/g, '<br>')}</div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Received:</strong> ${new Date(message.created_at).toLocaleString()}
                        </div>
                `;
                
                if (message.replies && message.replies.length > 0) {
                    content += '<div class="mb-3"><strong>Replies:</strong></div>';
                    message.replies.forEach(reply => {
                        const isInternal = reply.is_internal == 1;
                        content += `
                            <div class="reply-item border rounded p-3 mb-2 ${isInternal ? 'bg-warning bg-opacity-10' : 'bg-light'}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong>${reply.admin_username}</strong>
                                    <small class="text-muted">${new Date(reply.created_at).toLocaleString()}</small>
                                </div>
                                ${isInternal ? '<span class="badge bg-warning text-dark mb-2">Internal Note</span><br>' : ''}
                                ${reply.reply_message.replace(/\n/g, '<br>')}
                            </div>
                        `;
                    });
                }
                
                content += '</div>';
                document.getElementById('viewMessageContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('viewMessageModal')).show();
            } else {
                alert('Failed to load message details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load message details');
        });
}

// Reply to message
function replyToMessage(messageId) {
    document.getElementById('replyMessageId').value = messageId;
    new bootstrap.Modal(document.getElementById('replyModal')).show();
}

// Update message status
function updateStatus(messageId, status) {
    document.getElementById('updateStatusMessageId').value = messageId;
    document.getElementById('status').value = status;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}

// Delete message
function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_message">
            <input type="hidden" name="message_id" value="${messageId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-refresh page after form submission
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            // Add loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                submitBtn.disabled = true;
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
