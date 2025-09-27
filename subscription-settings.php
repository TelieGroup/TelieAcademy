<?php
require_once 'config/session.php';
require_once 'includes/User.php';
require_once 'includes/Newsletter.php';

$user = new User();
$newsletter = new Newsletter();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: index');
    exit;
}

$currentUser = $user->getCurrentUser();
$subscription = $newsletter->getUserSubscription($currentUser['id'], $currentUser['email']);

$pageTitle = "Subscription Settings";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Newsletter Subscription Settings
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($subscription && $subscription['is_active']): ?>
                            <!-- Current Subscription Status -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Current Plan</h5>
                                            <span class="badge bg-<?php echo $subscription['subscription_type'] === 'premium' ? 'warning' : 'success'; ?> fs-6 px-3 py-2">
                                                <?php echo ucfirst($subscription['subscription_type']); ?>
                                            </span>
                                            <?php if ($subscription['subscription_type'] === 'premium'): ?>
                                                <p class="text-muted mt-2 mb-0">
                                                    <small>$0/month</small>
                                                </p>
                                            <?php else: ?>
                                                <p class="text-muted mt-2 mb-0">
                                                    <small>Free</small>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Delivery Frequency</h5>
                                            <span class="badge bg-info fs-6 px-3 py-2">
                                                <?php echo ucfirst($subscription['frequency']); ?>
                                            </span>
                                            <p class="text-muted mt-2 mb-0">
                                                <small>Newsletter delivery</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subscription Details -->
                            <div class="card border-info mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Subscription Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Subscriber:</strong> <?php echo htmlspecialchars($subscription['name'] ?: $currentUser['username']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($subscription['email']); ?></p>
                                            <p><strong>Subscribed:</strong> <?php echo date('M j, Y', strtotime($subscription['subscribed_at'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if ($subscription['subscription_type'] === 'premium'): ?>
                                                <p><strong>Premium Started:</strong> <?php echo $subscription['premium_started_at'] ? date('M j, Y', strtotime($subscription['premium_started_at'])) : 'N/A'; ?></p>
                                                <p><strong>Premium Expires:</strong> 
                                                    <?php 
                                                    if ($subscription['premium_expires_at']) {
                                                        $expiryDate = new DateTime($subscription['premium_expires_at']);
                                                        $now = new DateTime();
                                                        if ($expiryDate > $now) {
                                                            echo '<span class="text-success">' . $expiryDate->format('M j, Y') . '</span>';
                                                        } else {
                                                            echo '<span class="text-danger">Expired</span>';
                                                        }
                                                    } else {
                                                        echo 'Never';
                                                    }
                                                    ?>
                                                </p>
                                            <?php endif; ?>
                                            <p><strong>Status:</strong> 
                                                <span class="badge bg-success">Active</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Preferences -->
                            <?php if ($subscription['preferences'] && is_array($subscription['preferences'])): ?>
                                <?php $preferences = $subscription['preferences']; ?>
                                <div class="card border-secondary mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Content Preferences</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php foreach ($preferences as $key => $value): ?>
                                                <div class="col-md-6 mb-2">
                                                    <i class="fas fa-<?php echo $value ? 'check-circle text-success' : 'times-circle text-muted'; ?> me-2"></i>
                                                    <?php echo ucwords(str_replace('_', ' ', $key)); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Plan Upgrade/Downgrade -->
                            <?php if ($subscription['subscription_type'] === 'newsletter'): ?>
                                <div class="card border-warning mb-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">Upgrade to Premium</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Unlock exclusive features with our Premium subscription:</p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-crown text-warning me-2"></i>Access to premium tutorials</li>
                                            <li><i class="fas fa-crown text-warning me-2"></i>Exclusive content</li>
                                            <li><i class="fas fa-crown text-warning me-2"></i>Priority support</li>
                                            <li><i class="fas fa-crown text-warning me-2"></i>Ad-free experience</li>
                                        </ul>
                                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#newsletterModal">
                                            <i class="fas fa-arrow-up me-2"></i>Upgrade to Premium ($0/month)
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Danger Zone -->
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0">Danger Zone</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        Unsubscribing will stop all newsletter emails. 
                                        <?php if ($subscription['subscription_type'] === 'premium'): ?>
                                            <strong>This will also revoke your premium access.</strong>
                                        <?php endif; ?>
                                    </p>
                                    <a href="unsubscribe.php" class="btn btn-outline-danger">
                                        <i class="fas fa-unlink me-2"></i>Unsubscribe from Newsletter
                                    </a>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- No Subscription -->
                            <div class="text-center py-5">
                                <i class="fas fa-envelope-open fa-4x text-muted mb-3"></i>
                                <h5>No Active Subscription</h5>
                                <p class="text-muted">You don't have an active newsletter subscription.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newsletterModal">
                                    <i class="fas fa-envelope me-2"></i>Subscribe to Newsletter
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Homepage
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Unsubscribe Confirmation Modal -->
    <div class="modal fade" id="unsubscribeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Unsubscribe
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to unsubscribe from the newsletter?</p>
                    <?php if ($subscription && $subscription['subscription_type'] === 'premium'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-crown me-2"></i>
                            <strong>Warning:</strong> This will also cancel your premium subscription and revoke access to premium content.
                        </div>
                    <?php endif; ?>
                    <div id="unsubscribeMessage" class="alert" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmUnsubscribe">
                        <i class="fas fa-unlink me-2"></i>Yes, Unsubscribe
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/modals.php'; ?>
    <?php include 'includes/scripts.php'; ?>

    <script>
    // Show unsubscribe confirmation modal
    function showUnsubscribeConfirm() {
        const modal = new bootstrap.Modal(document.getElementById('unsubscribeModal'));
        modal.show();
    }

    // Handle unsubscribe confirmation
    document.getElementById('confirmUnsubscribe').addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        const messageDiv = document.getElementById('unsubscribeMessage');
        
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Unsubscribing...';
        
        // Send unsubscribe request
        fetch('api/unsubscribe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success', messageDiv);
                
                // Reload page after successful unsubscribe
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showAlert(data.message, 'danger', messageDiv);
            }
        })
        .catch(error => {
            console.error('Unsubscribe error:', error);
            showAlert('Failed to unsubscribe. Please try again.', 'danger', messageDiv);
        })
        .finally(() => {
            // Restore button
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });
    </script>
</body>
</html>