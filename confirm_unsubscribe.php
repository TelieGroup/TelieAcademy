<?php
require_once 'config/database.php';
require_once 'includes/Newsletter.php';

$pageTitle = "Confirm Unsubscribe - TelieAcademy";
$message = '';
$messageType = '';
$showForm = false;

// Get email and token from URL
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($email) || empty($token)) {
    $message = 'Invalid confirmation link. Please check your email for the correct link.';
    $messageType = 'danger';
    $showForm = false;
} else {
    try {
        $newsletter = new Newsletter();
        
        // Verify the confirmation token
        $subscriber = $newsletter->getSubscriberByEmail($email);
        
        if (!$subscriber) {
            $message = 'Email address not found in our subscriber list.';
            $messageType = 'warning';
            $showForm = false;
        } elseif ($subscriber['unsubscribe_confirmation_token'] !== $token) {
            $message = 'Invalid confirmation token. Please check your email for the correct link.';
            $messageType = 'danger';
            $showForm = false;
        } elseif (!$subscriber['is_active']) {
            $message = 'You are already unsubscribed from our newsletter.';
            $messageType = 'info';
            $showForm = false;
        } else {
            // Process final unsubscribe
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                if ($action === 'confirm_unsubscribe') {
                    // Final unsubscribe
                    $result = $newsletter->unsubscribe($email);
                    
                    if ($result['success']) {
                        $message = 'You have been successfully unsubscribed from our newsletter. We\'re sorry to see you go!';
                        $messageType = 'success';
                        $showForm = false;
                        
                        // Log the unsubscribe reason and feedback if they exist
                        if (!empty($subscriber['unsubscribe_reason']) || !empty($subscriber['unsubscribe_feedback'])) {
                            $newsletter->logUnsubscribeFeedback($email, $subscriber['unsubscribe_reason'], $subscriber['unsubscribe_feedback']);
                        }
                        
                        // Clear the confirmation data
                        $newsletter->clearUnsubscribeConfirmation($email);
                        
                    } else {
                        $message = 'Failed to unsubscribe. Please try again or contact support.';
                        $messageType = 'danger';
                    }
                }
            } else {
                $showForm = true;
            }
        }
    } catch (Exception $e) {
        $message = 'An error occurred while processing your request. Please try again.';
        $messageType = 'danger';
        $showForm = false;
    }
}

include 'includes/head.php';
?>

<style>
.confirm-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 40px 20px;
}

.confirm-form {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.confirm-header {
    text-align: center;
    margin-bottom: 30px;
}

.confirm-header i {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 20px;
}

.confirm-header h1 {
    color: #343a40;
    margin-bottom: 10px;
}

.confirm-header p {
    color: #6c757d;
    font-size: 1.1rem;
}

.confirm-actions {
    text-align: center;
    margin-top: 30px;
}

.btn-confirm {
    background: #dc3545;
    border-color: #dc3545;
    padding: 12px 30px;
    font-size: 1.1rem;
    margin-right: 15px;
}

.btn-confirm:hover {
    background: #c82333;
    border-color: #c82333;
}

.btn-cancel {
    background: #6c757d;
    border-color: #6c757d;
    padding: 12px 30px;
    font-size: 1.1rem;
}

.btn-cancel:hover {
    background: #5a6268;
    border-color: #5a6268;
}

.alert {
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
}
</style>

<?php include 'includes/header.php'; ?>

<div class="container mt-5 pt-5">
    <div class="confirm-container">
        <div class="confirm-form">
            <div class="confirm-header">
                <i class="fas fa-envelope-open"></i>
                <h1>Confirm Unsubscribe</h1>
                <p>Please confirm your unsubscribe request</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($showForm): ?>
                <div class="text-center">
                    <h4 class="mb-4">Are you sure you want to unsubscribe?</h4>
                    <p class="text-muted mb-4">
                        You will no longer receive newsletters from TelieAcademy. 
                        You can resubscribe anytime using our signup form.
                    </p>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="confirm_unsubscribe">
                        
                        <div class="confirm-actions">
                            <button type="submit" class="btn btn-confirm">
                                <i class="fas fa-check me-2"></i>Yes, Unsubscribe Me
                            </button>
                            <a href="index" class="btn btn-cancel">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if ($messageType === 'success'): ?>
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-success mb-3">Unsubscribe Complete</h4>
                    <p class="text-muted mb-4">
                        You have been successfully unsubscribed from our newsletter.
                    </p>
                                            <a href="index" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Return to Home
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


