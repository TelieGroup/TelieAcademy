<?php
require_once 'config/database.php';
require_once 'includes/Newsletter.php';
require_once 'includes/User.php';

$pageTitle = "Unsubscribe - TelieAcademy";
$message = '';
$messageType = '';
$showForm = true;

// Check if user is logged in
$user = new User();
$isLoggedIn = $user->isLoggedIn();
$currentUser = null;
$userEmail = '';

if ($isLoggedIn) {
    $currentUser = $user->getCurrentUser();
    $userEmail = $currentUser['email'] ?? '';
}

// Get email and token from URL (optional - for direct unsubscribe links)
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// If both email and token are provided, this is a direct unsubscribe link
if (!empty($email) && !empty($token)) {
    try {
$newsletter = new Newsletter();

        // Verify the unsubscribe token
        $subscriber = $newsletter->getSubscriberByEmail($email);
        
        if (!$subscriber) {
            $message = 'Email address not found in our subscriber list.';
            $messageType = 'warning';
            $showForm = false;
        } elseif ($subscriber['unsubscribe_token'] !== $token) {
            $message = 'Invalid unsubscribe token. Please check your email for the correct link.';
            $messageType = 'danger';
            $showForm = false;
        } elseif (!$subscriber['is_active']) {
            $message = 'You are already unsubscribed from our newsletter.';
            $messageType = 'info';
            $showForm = false;
        } else {
            // Process direct unsubscribe
            $result = $newsletter->unsubscribe($email);
            
            if ($result['success']) {
                $message = 'You have been successfully unsubscribed from our newsletter.';
                $messageType = 'success';
                $showForm = false;
            } else {
                $message = 'Failed to unsubscribe. Please try again or contact support.';
                $messageType = 'danger';
            }
        }
    } catch (Exception $e) {
        $message = 'An error occurred while processing your request. Please try again.';
        $messageType = 'danger';
        $showForm = false;
    }
} else {
    // Show the unsubscribe form for users to enter their email
    $showForm = true;
    
    // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                if ($action === 'unsubscribe') {
            $email = $_POST['email'] ?? '';
                    $reason = $_POST['reason'] ?? '';
                    $feedback = $_POST['feedback'] ?? '';
            
            // If user is logged in, use their email if not provided in form
            if (empty($email) && $isLoggedIn && !empty($userEmail)) {
                $email = $userEmail;
            }
            
            if (empty($email)) {
                $message = 'Please enter your email address.';
                $messageType = 'danger';
            } else {
                try {
                    $newsletter = new Newsletter();
                    
                    // Check if email exists in subscribers
                    $subscriber = $newsletter->getSubscriberByEmail($email);
                    
                    if (!$subscriber) {
                        $message = 'Email address not found in our subscriber list.';
                        $messageType = 'warning';
                    } elseif (!$subscriber['is_active']) {
                        $message = 'You are already unsubscribed from our newsletter.';
                        $messageType = 'info';
                    } else {
                    // Generate confirmation token and send email
                    $confirmationToken = bin2hex(random_bytes(32));
                    $result = $newsletter->sendUnsubscribeConfirmation($email, $confirmationToken, $reason, $feedback);
                    
                    if ($result['success']) {
                        $message = 'We\'ve sent a confirmation email to your inbox. Please check your email and click the unsubscribe confirmation link to complete the process.';
                        $messageType = 'info';
                        $showForm = false;
                    } else {
                        $message = 'Failed to send confirmation email. Please try again or contact support.';
                        $messageType = 'danger';
                    }
                    }
                } catch (Exception $e) {
                    $message = 'An error occurred while processing your request. Please try again.';
                    $messageType = 'danger';
                }
            }
        }
    }
}

include 'includes/head.php';
?>

<style>
.unsubscribe-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 40px 20px;
}

.unsubscribe-form {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.unsubscribe-header {
    text-align: center;
    margin-bottom: 30px;
}

.unsubscribe-header i {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 20px;
}

.unsubscribe-header h1 {
    color: #343a40;
    margin-bottom: 10px;
}

.unsubscribe-header p {
    color: #6c757d;
    font-size: 1.1rem;
}

.feedback-section {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 10px;
    padding: 25px;
    margin: 25px 0;
}

.feedback-section h5 {
    color: #1976d2;
    margin-bottom: 15px;
}

.reason-options {
    margin-bottom: 20px;
}

.reason-options .form-check {
    margin-bottom: 10px;
}

.feedback-textarea {
    resize: vertical;
    min-height: 100px;
}

.unsubscribe-actions {
    text-align: center;
    margin-top: 30px;
}

.btn-unsubscribe {
    background: #dc3545;
    border-color: #dc3545;
    padding: 12px 30px;
    font-size: 1.1rem;
    font-weight: 500;
}

.btn-unsubscribe:hover {
    background: #c82333;
    border-color: #bd2130;
}

.btn-cancel {
    background: #6c757d;
    border-color: #6c757d;
    padding: 12px 30px;
    font-size: 1.1rem;
    margin-left: 15px;
}

.btn-cancel:hover {
    background: #5a6268;
    border-color: #545b62;
}

.success-message {
    text-align: center;
    padding: 40px;
}

.success-message i {
    font-size: 4rem;
    color: #28a745;
    margin-bottom: 20px;
}

.success-message h2 {
    color: #28a745;
    margin-bottom: 15px;
}

.success-message p {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 25px;
}

/* Dark Mode Support */
.dark-mode .unsubscribe-form {
    background: #2d2d2d;
    color: #e0e0e0;
}

.dark-mode .unsubscribe-header h1 {
    color: #e0e0e0;
}

.dark-mode .unsubscribe-header p {
    color: #b0b0b0;
}

.dark-mode .feedback-section {
    background: #1e3a5f;
    border-color: #2196f3;
    color: #e0e0e0;
}

.dark-mode .feedback-section h5 {
    color: #64b5f6;
}

.dark-mode .form-control,
.dark-mode .form-select {
    background: #404040;
    border-color: #505050;
    color: #e0e0e0;
}

.dark-mode .form-control:focus,
.dark-mode .form-select:focus {
    background: #404040;
    border-color: #007bff;
    color: #e0e0e0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.dark-mode .form-label {
    color: #e0e0e0;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-info .alert-link {
    color: #0a3c44;
    text-decoration: underline;
}

.dark-mode .alert-info {
    background-color: #1e3a5f;
    border-color: #2196f3;
    color: #64b5f6;
}

.dark-mode .alert-info .alert-link {
    color: #90caf9;
}
</style>

    <?php include 'includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="unsubscribe-container">
        <?php if ($showForm): ?>
            <!-- Unsubscribe Form -->
            <div class="unsubscribe-form">
                <?php if ($isLoggedIn && !empty($userEmail)): ?>
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Logged in as:</strong> <?php echo htmlspecialchars($currentUser['username'] ?? $userEmail); ?>
                        <br>
                        <small class="text-muted">
                            You can also manage your subscription preferences in your 
                            <a href="subscription-settings" class="alert-link">account settings</a>.
                        </small>
                    </div>
                <?php endif; ?>
                
                <div class="unsubscribe-header">
                    <i class="fas fa-envelope-open"></i>
                    <h1>Unsubscribe from Newsletter</h1>
                    <?php if ($isLoggedIn && !empty($userEmail)): ?>
                        <p>We're sorry to see you go, <strong><?php echo htmlspecialchars($currentUser['username'] ?? $userEmail); ?></strong>. Please provide feedback below.</p>
                    <?php else: ?>
                        <p>Enter your email address and provide feedback to unsubscribe</p>
                    <?php endif; ?>
                    </div>

                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="unsubscribe">
                    
                    <!-- Email Input Section -->
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address *</label>
                        <?php if ($isLoggedIn && !empty($userEmail)): ?>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
                            <div class="form-text">
                                <i class="fas fa-user-check text-success me-1"></i>
                                Using your logged-in account email: <strong><?php echo htmlspecialchars($userEmail); ?></strong>
                            </div>
                        <?php else: ?>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   placeholder="Enter your email address" required>
                            <div class="form-text">Enter the email address you used to subscribe to our newsletter.</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Feedback Section -->
                    <div class="feedback-section">
                        <h5><i class="fas fa-comment me-2"></i>Help us improve</h5>
                        <p class="mb-3">Your feedback helps us create better content and improve our newsletter.</p>
                        
                        <!-- Reason for unsubscribing -->
                        <div class="mb-3">
                            <label class="form-label">Why are you unsubscribing?</label>
                            <div class="reason-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reason" id="reason_too_frequent" value="too_frequent">
                                    <label class="form-check-label" for="reason_too_frequent">
                                        Emails are too frequent
                                    </label>
                            </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reason" id="reason_not_relevant" value="not_relevant">
                                    <label class="form-check-label" for="reason_not_relevant">
                                        Content is not relevant to me
                                    </label>
                                        </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reason" id="reason_quality" value="quality">
                                    <label class="form-check-label" for="reason_quality">
                                        Content quality is poor
                                    </label>
                                        </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reason" id="reason_spam" value="spam">
                                    <label class="form-check-label" for="reason_spam">
                                        Marked as spam by mistake
                                    </label>
                                    </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reason" id="reason_other" value="other">
                                    <label class="form-check-label" for="reason_other">
                                        Other reason
                                    </label>
                                        </div>
                                </div>
                            </div>

                        <!-- Additional feedback -->
                                <div class="mb-3">
                            <label for="feedback" class="form-label">Additional feedback (optional)</label>
                            <textarea class="form-control feedback-textarea" id="feedback" name="feedback" 
                                      placeholder="Please share any additional thoughts or suggestions..."></textarea>
                                    </div>
                                </div>
                                
                    <!-- Unsubscribe Actions -->
                    <div class="unsubscribe-actions">
                        <button type="submit" class="btn btn-unsubscribe btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Send Confirmation Email
                        </button>
                        <a href="index" class="btn btn-cancel btn-lg">
                                        <i class="fas fa-arrow-left me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
            </div>
        <?php else: ?>
            <!-- Success/Error Message -->
            <div class="unsubscribe-form">
                <div class="success-message">
                    <?php if ($messageType === 'success'): ?>
                        <i class="fas fa-check-circle"></i>
                        <h2>Unsubscribed Successfully</h2>
                        <p><?php echo htmlspecialchars($message); ?></p>
                        <p>You can always resubscribe in the future if you change your mind.</p>
                        <a href="index" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Return to Home
                        </a>
                        <?php else: ?>
                        <i class="fas fa-exclamation-triangle"></i>
                        <h2><?php echo $messageType === 'danger' ? 'Error' : 'Notice'; ?></h2>
                        <p><?php echo htmlspecialchars($message); ?></p>
                        <a href="index" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Return to Home
                        </a>
                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

<script>
// Show/hide feedback textarea based on reason selection
document.querySelectorAll('input[name="reason"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const feedbackTextarea = document.getElementById('feedback');
        if (this.value === 'other') {
            feedbackTextarea.style.display = 'block';
            feedbackTextarea.required = true;
        } else {
            feedbackTextarea.style.display = 'none';
            feedbackTextarea.required = false;
        }
    });
});

// Auto-resize feedback textarea
document.getElementById('feedback')?.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});
</script>

    <?php include 'includes/footer.php'; ?>
