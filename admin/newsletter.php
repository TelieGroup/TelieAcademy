<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Newsletter.php';

$user = new User();
$newsletter = new Newsletter();

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
    
    if ($postAction === 'send_newsletter' && isset($_POST['subject']) && isset($_POST['content'])) {
        try {
            $subject = $_POST['subject'];
            $content = $_POST['content'];
            $subscribers = $newsletter->getActiveSubscribers();
            
            // For now, we'll just log the newsletter
            // In a real implementation, you'd send emails here
            $result = ['success' => true, 'message' => 'Newsletter sent to ' . count($subscribers) . ' subscribers'];
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => 'Error sending newsletter: ' . $e->getMessage()];
        }
    } elseif ($postAction === 'delete_subscriber' && isset($_POST['subscriber_id'])) {
        try {
            $subscriberId = $_POST['subscriber_id'];
            $result = $newsletter->deleteSubscriber($subscriberId);
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => 'Error deleting subscriber: ' . $e->getMessage()];
        }
    } elseif ($postAction === 'delete_feedback' && isset($_POST['feedback_id'])) {
        try {
            $feedbackId = $_POST['feedback_id'];
            $result = $newsletter->deleteUnsubscribeFeedback($feedbackId);
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => 'Error deleting feedback: ' . $e->getMessage()];
        }
    } elseif ($postAction === 'clear_all_feedback') {
        try {
            $result = $newsletter->clearAllUnsubscribeFeedback();
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => 'Error clearing feedback: ' . $e->getMessage()];
        }
    }
}

// Set page variables for head component
$pageTitle = 'Newsletter Management';
$pageDescription = 'Manage newsletter subscribers and send newsletters';

include '../includes/head.php';
?>
<!-- Admin CSS -->
<link rel="stylesheet" href="admin.css">

<style>
/* Newsletter Page Specific Dark Mode Support */
.dark-mode .container-fluid {
    background: #1a1a1a;
    color: #e0e0e0;
}

.dark-mode .card {
    background: #2d2d2d;
    border-color: #404040;
    color: #e0e0e0;
}

.dark-mode .card-header {
    background: #353535;
    border-bottom-color: #404040;
    color: #e0e0e0;
}

.dark-mode .table {
    background: #2d2d2d;
    color: #e0e0e0;
}

.dark-mode .table th {
    background: #353535;
    color: #ffffff;
    border-color: #404040;
}

.dark-mode .table td {
    border-color: #404040;
    color: #e0e0e0;
}

.dark-mode .table tbody tr:hover {
    background: #353535;
}

.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > td {
    background-color: #2d2d2d;
}

.dark-mode .table-striped > tbody > tr:nth-of-type(odd):hover > td {
    background-color: #353535;
}

.dark-mode .form-control,
.dark-mode .form-select {
    background: #404040;
    border-color: #505050;
    color: #e0e0e0;
}

.dark-mode .form-control:focus,
.dark-mode .form-control:focus {
    background: #404040;
    border-color: #0d6efd;
    color: #e0e0e0;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.dark-mode .form-label {
    color: #e0e0e0;
}

.dark-mode .text-muted {
    color: #b0b0b0 !important;
}

.dark-mode .border-bottom {
    border-bottom-color: #404040 !important;
}

.dark-mode .btn-toolbar .btn {
    background: #404040;
    border-color: #505050;
    color: #e0e0e0;
}

.dark-mode .btn-toolbar .btn:hover {
    background: #505050;
    border-color: #606060;
    color: #ffffff;
}

.dark-mode .btn-toolbar .btn-primary {
    background: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.dark-mode .btn-toolbar .btn-primary:hover {
    background: #0b5ed7;
    border-color: #0b5ed7;
    color: white;
}

/* Unsubscribe Feedback Styles */
.feedback-text {
    max-width: 200px;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.feedback-text:hover {
    cursor: pointer;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 0.5rem 1rem;
    margin-right: 0.5rem;
}

.nav-tabs .nav-link.active {
    color: #495057;
    background-color: transparent;
    border-bottom-color: #007bff;
    font-weight: 500;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #dee2e6;
    color: #495057;
}

.dark-mode .nav-tabs .nav-link {
    color: #b0b0b0;
    border-bottom-color: transparent;
}

.dark-mode .nav-tabs .nav-link.active {
    color: #e0e0e0;
    border-bottom-color: #007bff;
}

.dark-mode .nav-tabs .nav-link:hover {
    border-bottom-color: #404040;
    color: #e0e0e0;
}

.badge.bg-warning.text-dark {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.dark-mode .badge.bg-warning.text-dark {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

/* Badge transition effects */
#unsubscribe-badge {
    transition: opacity 0.3s ease-in-out;
}

#unsubscribe-badge.hidden {
    opacity: 0;
    pointer-events: none;
}

/* Feedback Details Modal Styling */
.feedback-details {
    font-size: 14px;
}

.feedback-details .row {
    margin-bottom: 1rem;
}

.feedback-details strong {
    color: #495057;
    font-weight: 600;
}

.feedback-details .bg-light {
    background-color: #f8f9fa !important;
    border: 1px solid #e9ecef;
    font-family: 'Courier New', monospace;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.dark-mode .feedback-details strong {
    color: #e0e0e0;
}

.dark-mode .feedback-details .bg-light {
    background-color: #2d2d2d !important;
    border-color: #404040;
    color: #e0e0e0;
}
</style>
<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if (isset($result)): ?>
            <div class="alert alert-<?php echo $result['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($result['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    Newsletter Management
                    <?php
                    try {
                        $pendingSubscriptionCount = $newsletter->getPendingSubscriptionCount();
                        $totalSubscribers = $newsletter->getTotalSubscriberCount();
                        if ($pendingSubscriptionCount > 0):
                        ?>
                        <span class="badge bg-warning text-dark ms-2"><?php echo $pendingSubscriptionCount; ?> Pending</span>
                        <?php endif; ?>
                        <span class="badge bg-info ms-2"><?php echo $totalSubscribers; ?> Total</span>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sendNewsletterModal">
                        <i class="fas fa-paper-plane me-1"></i>Send Newsletter
                    </button>
                    <a href="send_newsletter" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="fas fa-edit me-1"></i>Compose Newsletter
                    </a>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4" id="newsletterTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="subscribers-tab" data-bs-toggle="tab" data-bs-target="#subscribers" type="button" role="tab" aria-controls="subscribers" aria-selected="true">
                        <i class="fas fa-users me-1"></i>Subscribers
                        <?php
                        try {
                            $pendingSubscriptionCount = $newsletter->getPendingSubscriptionCount();
                            if ($pendingSubscriptionCount > 0):
                            ?>
                            <span class="badge bg-warning text-dark ms-1"><?php echo $pendingSubscriptionCount; ?></span>
                            <?php endif; ?>
                        <?php } catch (Exception $e) { /* Silently fail */ } ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="unsubscribe-feedback-tab" data-bs-toggle="tab" data-bs-target="#unsubscribe-feedback" type="button" role="tab" aria-controls="unsubscribe-feedback" aria-selected="false">
                        <i class="fas fa-comment-dots me-1"></i>Unsubscribe Feedback
                        <?php
                        try {
                            $unviewedCount = $newsletter->getUnviewedUnsubscribeFeedbackCount();
                            if ($unviewedCount > 0):
                            ?>
                            <span class="badge bg-danger ms-1"><?php echo $unviewedCount; ?></span>
                            <?php endif; ?>
                        <?php } catch (Exception $e) {
                            // Silently fail for the badge count
                        } catch (Error $e) {
                            // Silently fail for the badge count
                        } ?>
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="newsletterTabsContent">
                <!-- Subscribers Tab -->
                <div class="tab-pane fade show active" id="subscribers" role="tabpanel" aria-labelledby="subscribers-tab">
            <div class="row">
                <div class="col-md-8">
                    <!-- Subscribers List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Newsletter Subscribers</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $subscribers = $newsletter->getActiveSubscribers();
                            ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Subscribed Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($subscribers)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                <i class="fas fa-envelope fa-2x mb-2"></i>
                                                <p>No subscribers found.</p>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($subscribers as $subscriber): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($subscriber['subscribed_at'])); ?></td>
                                            <td>
                                                <?php if ($subscriber['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteSubscriber(<?php echo $subscriber['id']; ?>, '<?php echo htmlspecialchars($subscriber['email']); ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Newsletter Statistics -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Newsletter Statistics</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $stats = $newsletter->getNewsletterStatistics();
                            ?>
                            <div class="mb-3">
                                <label class="form-label">Total Subscribers</label>
                                <h4 class="text-primary mb-0"><?php echo $stats['total_subscribers'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Active Subscribers</label>
                                <h4 class="text-success mb-0"><?php echo $stats['active_subscribers'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Recent Subscriber</label>
                                <p class="text-muted mb-0 small"><?php echo $stats['recent_subscriber'] ?? 'No subscribers'; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sendNewsletterModal">
                                    <i class="fas fa-paper-plane me-1"></i>Send Newsletter
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="exportSubscribers()">
                                    <i class="fas fa-download me-1"></i>Export Subscribers
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="importSubscribers()">
                                    <i class="fas fa-upload me-1"></i>Import Subscribers
                                </button>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            
            <!-- Unsubscribe Feedback Tab -->
            <div class="tab-pane fade" id="unsubscribe-feedback" role="tabpanel" aria-labelledby="unsubscribe-feedback-tab">
                <?php
                // Mark all current feedback as viewed when admin opens this tab
                try {
                    $newsletter->markAllFeedbackAsViewed();
                } catch (Exception $e) {
                    // Silently fail
                }
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                                                            <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-comment-dots me-2"></i>Unsubscribe Feedback
                                    </h5>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportUnsubscribeFeedback()">
                                            <i class="fas fa-download me-1"></i>Export
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllFeedback()">
                                            <i class="fas fa-trash me-1"></i>Clear All
                                        </button>
                                    </div>
                                </div>
                                                            <div class="card-body">
                                    <?php
                                    try {
                                        $unsubscribeFeedback = $newsletter->getUnsubscribeFeedback();
                                    } catch (Exception $e) {
                                        echo '<div class="alert alert-danger">Error retrieving unsubscribe feedback: ' . htmlspecialchars($e->getMessage()) . '</div>';
                                        $unsubscribeFeedback = [];
                                    } catch (Error $e) {
                                        echo '<div class="alert alert-danger">Fatal error retrieving unsubscribe feedback: ' . htmlspecialchars($e->getMessage()) . '</div>';
                                        $unsubscribeFeedback = [];
                                    }
                                    ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Email</th>
                                                <th>Reason</th>
                                                <th>Feedback</th>
                                                <th>Unsubscribed Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($unsubscribeFeedback)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    <i class="fas fa-comment-slash fa-2x mb-2"></i>
                                                    <p>No unsubscribe feedback found.</p>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($unsubscribeFeedback as $feedback): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($feedback['email'] ?? ''); ?></strong>
                                                    <?php if (!empty($feedback['username'])): ?>
                                                        <br><small class="text-muted">@<?php echo htmlspecialchars($feedback['username']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $reasonLabels = [
                                                        'too_frequent' => 'Too Frequent',
                                                        'not_relevant' => 'Not Relevant',
                                                        'quality' => 'Poor Quality',
                                                        'spam' => 'Marked as Spam',
                                                        'other' => 'Other'
                                                    ];
                                                    $unsubscribeReason = $feedback['unsubscribe_reason'] ?? $feedback['reason'] ?? 'unknown';
                                                    $reasonLabel = $reasonLabels[$unsubscribeReason] ?? 'Unknown';
                                                    ?>
                                                    <span class="badge bg-warning text-dark"><?php echo $reasonLabel; ?></span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $feedbackText = $feedback['unsubscribe_feedback'] ?? $feedback['feedback'] ?? '';
                                                    if (!empty($feedbackText)): 
                                                    ?>
                                                        <div class="feedback-text" style="max-width: 200px;">
                                                            <?php echo htmlspecialchars($feedbackText); ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">No additional feedback</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $feedbackDate = $feedback['unsubscribe_requested_at'] ?? $feedback['created_at'] ?? 'now';
                                                    echo date('M j, Y', strtotime($feedbackDate)); 
                                                    ?>
                                                    <br><small class="text-muted"><?php echo date('g:i A', strtotime($feedbackDate)); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="viewFeedbackDetails(<?php echo $feedback['id'] ?? 0; ?>)">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteFeedback(<?php echo $feedback['id'] ?? 0; ?>)">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Send Newsletter Modal -->
<div class="modal fade" id="sendNewsletterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Newsletter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="send_newsletter">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newsletter_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="newsletter_subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="newsletter_content" class="form-label">Content</label>
                        <textarea class="form-control" id="newsletter_content" name="content" rows="10" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recipients</label>
                        <p class="text-muted">This newsletter will be sent to <?php echo $newsletter->getUnsubscribeFeedbackCount(); ?> active subscribers.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Send Newsletter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Feedback Details Modal -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-comment-dots me-2"></i>Feedback Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="feedbackModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="deleteFeedbackBtn" onclick="deleteFeedbackFromModal()">
                    <i class="fas fa-trash me-1"></i>Delete Feedback
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Delete subscriber function
function deleteSubscriber(subscriberId, email) {
    if (confirm('Are you sure you want to delete the subscriber "' + email + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'newsletter';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_subscriber';
        
        const subscriberIdInput = document.createElement('input');
        subscriberIdInput.type = 'hidden';
        subscriberIdInput.name = 'subscriber_id';
        subscriberIdInput.value = subscriberId;
        
        form.appendChild(actionInput);
        form.appendChild(subscriberIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Export subscribers function
function exportSubscribers() {
    // This would typically make an AJAX call to export subscribers
    alert('Export functionality would be implemented here.');
}

// Import subscribers function
function importSubscribers() {
    // This would typically make an AJAX call to import subscribers
    alert('Import functionality would be implemented here.');
}

// Unsubscribe Feedback Functions
function viewFeedbackDetails(feedbackId) {
    // Get feedback data and populate modal
    fetch(`get_feedback_details?id=${feedbackId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const feedback = data.feedback;
                const modalBody = document.getElementById('feedbackModalBody');
                
                // Create feedback details HTML
                const feedbackHtml = `
                    <div class="feedback-details">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Email:</strong> ${feedback.email}
                            </div>
                            <div class="col-md-6">
                                <strong>Username:</strong> ${feedback.username || 'Guest User'}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Reason:</strong> 
                                <span class="badge bg-warning text-dark">${getReasonLabel(feedback.unsubscribe_reason)}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Date:</strong> ${formatDate(feedback.unsubscribe_requested_at)}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <strong>Additional Feedback:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    ${feedback.unsubscribe_feedback || 'No additional feedback provided'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                modalBody.innerHTML = feedbackHtml;
                
                // Store feedback ID for delete function
                document.getElementById('deleteFeedbackBtn').setAttribute('data-feedback-id', feedbackId);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('viewFeedbackModal'));
                modal.show();
            } else {
                alert('Error loading feedback details: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading feedback details. Please try again.');
        });
}

function deleteFeedback(feedbackId) {
    if (confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'newsletter';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_feedback';
        
        const feedbackIdInput = document.createElement('input');
        feedbackIdInput.type = 'hidden';
        feedbackIdInput.name = 'feedback_id';
        feedbackIdInput.value = feedbackId;
        
        form.appendChild(actionInput);
        form.appendChild(feedbackIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteFeedbackFromModal() {
    const feedbackId = document.getElementById('deleteFeedbackBtn').getAttribute('data-feedback-id');
    if (feedbackId) {
        // Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('viewFeedbackModal'));
        modal.hide();
        
        // Then delete
        deleteFeedback(feedbackId);
    }
}

// Helper functions
function getReasonLabel(reason) {
    const reasonLabels = {
        'too_frequent': 'Too Frequent',
        'not_relevant': 'Not Relevant',
        'quality': 'Poor Quality',
        'spam': 'Marked as Spam',
        'other': 'Other'
    };
    return reasonLabels[reason] || 'Unknown';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function exportUnsubscribeFeedback() {
    alert('Export unsubscribe feedback functionality will be implemented here.');
}

function clearAllFeedback() {
    if (confirm('Are you sure you want to clear all unsubscribe feedback? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'newsletter';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'clear_all_feedback';
        
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}





// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tabs
    const triggerTabList = [].slice.call(document.querySelectorAll('#newsletterTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl)
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
});
</script>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?> 