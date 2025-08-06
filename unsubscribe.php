<?php
require_once 'config/session.php';
require_once 'includes/User.php';
require_once 'includes/Newsletter.php';

$user = new User();
$newsletter = new Newsletter();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$currentUser = $user->getCurrentUser();
$subscription = $newsletter->getUserSubscription($currentUser['id'], $currentUser['email']);

// Handle unsubscribe request
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_unsubscribe'])) {
        $result = $newsletter->unsubscribe($currentUser['email']);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            
            // Refresh subscription data
            $subscription = null;
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

$pageTitle = "Unsubscribe from Newsletter";
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
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark text-center">
                        <h4 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Unsubscribe from Newsletter
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($subscription && $subscription['is_active']): ?>
                            <!-- Active Subscription - Show Unsubscribe Form -->
                            <div class="text-center mb-4">
                                <i class="fas fa-envelope fa-4x text-warning mb-3"></i>
                                <h5>Current Subscription</h5>
                            </div>

                            <div class="card border-info mb-4">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-6">
                                            <h6>Subscription Type</h6>
                                            <span class="badge bg-<?php echo $subscription['subscription_type'] === 'premium' ? 'warning' : 'success'; ?> fs-6">
                                                <?php echo ucfirst($subscription['subscription_type']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Frequency</h6>
                                            <span class="badge bg-info fs-6">
                                                <?php echo ucfirst($subscription['frequency']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($subscription['subscription_type'] === 'premium'): ?>
                                        <hr>
                                        <div class="text-center">
                                            <p class="mb-0">
                                                <strong>Premium Expires:</strong> 
                                                <?php 
                                                if ($subscription['premium_expires_at']) {
                                                    echo date('M j, Y', strtotime($subscription['premium_expires_at']));
                                                } else {
                                                    echo 'Never';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Warning Message -->
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Are you sure?</h6>
                                <p class="mb-2">Unsubscribing will:</p>
                                <ul class="mb-2">
                                    <li>Stop all newsletter emails</li>
                                    <li>Remove you from our mailing list</li>
                                    <?php if ($subscription['subscription_type'] === 'premium'): ?>
                                        <li><strong>Cancel your premium subscription and revoke access to premium content</strong></li>
                                    <?php endif; ?>
                                </ul>
                                <p class="mb-0"><strong>This action cannot be undone.</strong></p>
                            </div>

                            <!-- Unsubscribe Form -->
                            <form method="POST" class="text-center">
                                <div class="mb-3">
                                    <div class="form-check d-inline-block">
                                        <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                                        <label class="form-check-label" for="confirmCheck">
                                            I understand the consequences and want to unsubscribe
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <a href="profile.php" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-arrow-left me-2"></i>Cancel
                                    </a>
                                    <button type="submit" name="confirm_unsubscribe" class="btn btn-danger">
                                        <i class="fas fa-unlink me-2"></i>Yes, Unsubscribe
                                    </button>
                                </div>
                            </form>

                        <?php else: ?>
                            <!-- No Active Subscription -->
                            <div class="text-center py-4">
                                <i class="fas fa-envelope-open fa-4x text-muted mb-3"></i>
                                <h5>No Active Subscription</h5>
                                <p class="text-muted mb-4">You don't have an active newsletter subscription to unsubscribe from.</p>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <a href="index.php" class="btn btn-primary me-md-2">
                                        <i class="fas fa-home me-2"></i>Go to Homepage
                                    </a>
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newsletterModal">
                                        <i class="fas fa-envelope me-2"></i>Subscribe to Newsletter
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Alternative Options -->
                <?php if ($subscription && $subscription['is_active']): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Alternative Options</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Instead of unsubscribing completely, you might want to:</p>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="subscription-settings.php" class="btn btn-outline-info me-md-2">
                                <i class="fas fa-cog me-2"></i>Change Preferences
                            </a>
                            <?php if ($subscription['subscription_type'] === 'premium'): ?>
                                <a href="subscription-settings.php" class="btn btn-outline-warning">
                                    <i class="fas fa-arrow-down me-2"></i>Downgrade to Free Newsletter
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/modals.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>