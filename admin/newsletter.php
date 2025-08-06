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
    }
}

// Set page variables for head component
$pageTitle = 'Newsletter Management';
$pageDescription = 'Manage newsletter subscribers and send newsletters';

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
            <?php if (isset($result)): ?>
            <div class="alert alert-<?php echo $result['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($result['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Newsletter Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sendNewsletterModal">
                        <i class="fas fa-paper-plane me-1"></i>Send Newsletter
                    </button>
                </div>
            </div>

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
                        <p class="text-muted">This newsletter will be sent to <?php echo $newsletter->getSubscriberCount(); ?> active subscribers.</p>
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

<script>
// Delete subscriber function
function deleteSubscriber(subscriberId, email) {
    if (confirm('Are you sure you want to delete the subscriber "' + email + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'newsletter.php';
        
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
</script>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?> 