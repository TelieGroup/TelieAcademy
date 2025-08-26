<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Newsletter.php';
require_once '../includes/EmailHelper.php';

$user = new User();

// Check if user is logged in and is admin
if (!$user->isLoggedIn()) {
    header('Location: ../index');
    exit;
}

$currentUser = $user->getCurrentUser();
if (!$currentUser || !$currentUser['is_admin']) {
    header('Location: ../index');
    exit;
}

// Initialize classes
$newsletter = new Newsletter();
$emailHelper = new EmailHelper();

$pageTitle = "Send Newsletter - Admin";
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send_newsletter') {
        $subject = trim($_POST['subject'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $template = $_POST['template'] ?? 'default';
        $targetAudience = $_POST['target_audience'] ?? 'all';
        $testEmail = trim($_POST['test_email'] ?? '');
        
        // Validation
        if (empty($subject) || empty($content)) {
            $message = 'Subject and content are required.';
            $messageType = 'danger';
        } else {
            try {
                // If test email is provided, send test first
                if (!empty($testEmail)) {
                    $testResult = $emailHelper->testEmail($testEmail, $subject, $content);
                    if (!$testResult['success']) {
                        $message = 'Test email failed: ' . $testResult['message'];
                        $messageType = 'danger';
                    } else {
                        $message = 'Test email sent successfully! Check your inbox.';
                        $messageType = 'success';
                    }
                } else {
                    // Get subscribers based on target audience
                    $subscribers = null;
                    if ($targetAudience === 'premium') {
                        $subscribers = $newsletter->getSubscribersByType('premium');
                    } elseif ($targetAudience === 'newsletter') {
                        $subscribers = $newsletter->getSubscribersByType('newsletter');
                    }
                    
                    // Send newsletter
                    $result = $emailHelper->sendNewsletter($subject, $content, $subscribers, $template);
                    
                    if ($result['success']) {
                        $message = $result['message'];
                        $messageType = 'success';
                        
                        // Clear form
                        $_POST = [];
                    } else {
                        $message = $result['message'];
                        $messageType = 'danger';
                    }
                }
            } catch (Exception $e) {
                $message = 'Error sending newsletter: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}

// Get subscriber statistics
$stats = $newsletter->getNewsletterStatistics();
$totalSubscribers = $stats['total_subscribers'] ?? 0;
$activeSubscribers = $stats['active_subscribers'] ?? 0;

// Get premium subscribers count
$premiumSubscribers = count($newsletter->getSubscribersByType('premium'));

// Check mail server status
$mailStatus = $emailHelper->getMailServerStatus();

include '../includes/head.php';
?>

<!-- Admin CSS -->
<link rel="stylesheet" href="admin.css">

<style>
.newsletter-form {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 30px;
    margin-bottom: 30px;
}

.template-preview {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin: 15px 0;
    background: white;
}

.template-preview h5 {
    color: #495057;
    margin-bottom: 15px;
}

.template-preview .preview-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}

.stats-cards {
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 10px;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.premium-stat {
    border-left: 4px solid #ffc107;
}

.newsletter-stat {
    border-left: 4px solid #28a745;
}

.total-stat {
    border-left: 4px solid #007bff;
}

.content-editor {
    min-height: 300px;
    resize: vertical;
}

.test-email-section {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.target-audience-section {
    background: #f3e5f5;
    border: 1px solid #9c27b0;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

/* Dark Mode Support */
.dark-mode .newsletter-form {
    background: #2d2d2d;
    color: #e0e0e0;
}

.dark-mode .template-preview {
    background: #353535;
    border-color: #404040;
    color: #e0e0e0;
}

.dark-mode .template-preview .preview-content {
    background: #404040;
    border-left-color: #007bff;
}

.dark-mode .stat-card {
    background: #2d2d2d;
    color: #e0e0e0;
    border: 1px solid #404040;
}

.dark-mode .test-email-section {
    background: #1e3a5f;
    border-color: #2196f3;
    color: #e0e0e0;
}

.dark-mode .target-audience-section {
    background: #3d2e4a;
    border-color: #9c27b0;
    color: #e0e0e0;
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
</style>

<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-envelope-open-text me-2"></i>Send Newsletter</h1>
                <div>
                    <a href="newsletter" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Newsletter Management
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card total-stat">
                            <div class="stat-number"><?php echo $totalSubscribers; ?></div>
                            <div class="stat-label">Total Subscribers</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card newsletter-stat">
                            <div class="stat-number"><?php echo $activeSubscribers; ?></div>
                            <div class="stat-label">Active Subscribers</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card premium-stat">
                            <div class="stat-number"><?php echo $premiumSubscribers; ?></div>
                            <div class="stat-label">Premium Members</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter Form -->
            <div class="newsletter-form">
                <h3 class="mb-4"><i class="fas fa-paper-plane me-2"></i>Compose Newsletter</h3>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="send_newsletter">
                    
                    <!-- Subject -->
                    <div class="mb-3">
                        <label for="subject" class="form-label">Newsletter Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" 
                               placeholder="Enter newsletter subject..." required>
                        <div class="form-text">Keep it engaging and descriptive</div>
                    </div>

                    <!-- Template Selection -->
                    <div class="mb-3">
                        <label for="template" class="form-label">Email Template</label>
                        <select class="form-select" id="template" name="template">
                            <option value="default" <?php echo ($_POST['template'] ?? 'default') === 'default' ? 'selected' : ''; ?>>Default Template</option>
                            <option value="minimal" <?php echo ($_POST['template'] ?? 'default') === 'minimal' ? 'selected' : ''; ?>>Minimal Template</option>
                            <option value="premium" <?php echo ($_POST['template'] ?? 'default') === 'premium' ? 'selected' : ''; ?>>Premium Template</option>
                        </select>
                        <div class="form-text">Choose the visual style for your newsletter</div>
                    </div>

                    <!-- Target Audience -->
                    <div class="target-audience-section">
                        <h5><i class="fas fa-users me-2"></i>Target Audience</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target_audience" id="audience_all" value="all" 
                                           <?php echo ($_POST['target_audience'] ?? 'all') === 'all' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="audience_all">
                                        <strong>All Subscribers</strong> (<?php echo $totalSubscribers; ?> recipients)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target_audience" id="audience_newsletter" value="newsletter" 
                                           <?php echo ($_POST['target_audience'] ?? 'all') === 'newsletter' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="audience_newsletter">
                                        <strong>Newsletter Only</strong> (<?php echo $activeSubscribers - $premiumSubscribers; ?> recipients)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target_audience" id="audience_premium" value="premium" 
                                           <?php echo ($_POST['target_audience'] ?? 'all') === 'premium' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="audience_premium">
                                        <strong>Premium Members Only</strong> (<?php echo $premiumSubscribers; ?> recipients)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Newsletter Content *</label>
                        <textarea class="form-control content-editor" id="content" name="content" rows="12" 
                                  placeholder="Write your newsletter content here... You can use HTML tags for formatting." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                        <div class="form-text">
                            <strong>HTML Tips:</strong> Use &lt;h1&gt;, &lt;h2&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;a&gt; tags for formatting
                        </div>
                    </div>

                    <!-- Test Email Section -->
                    <div class="test-email-section">
                        <h5><i class="fas fa-vial me-2"></i>Test Email (Optional)</h5>
                        <p class="mb-3">Send a test email to verify the newsletter looks correct before sending to all subscribers.</p>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="email" class="form-control" name="test_email" 
                                       placeholder="Enter email address for testing..." 
                                       value="<?php echo htmlspecialchars($_POST['test_email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-info w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Send Test Email
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Send Newsletter Button -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Are you sure you want to send this newsletter to all selected subscribers? This action cannot be undone.')">
                            <i class="fas fa-rocket me-2"></i>Send Newsletter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Template Previews -->
            <div class="template-preview">
                <h5><i class="fas fa-eye me-2"></i>Template Preview</h5>
                <div class="preview-content">
                    <p><strong>Default Template:</strong> Professional design with TelieAcademy branding, gradient header, and call-to-action buttons.</p>
                    <p><strong>Minimal Template:</strong> Clean, simple design focused on content readability.</p>
                    <p><strong>Premium Template:</strong> Exclusive design with premium branding and special features for premium members.</p>
                </div>
            </div>

            <!-- Mail Server Status -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-server me-2"></i>Mail Server Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Current Configuration:</h6>
                            <ul class="list-unstyled">
                                <li><strong>SMTP Host:</strong> <?php echo $mailStatus['smtp_host'] ?: 'Not set'; ?></li>
                                <li><strong>SMTP Port:</strong> <?php echo $mailStatus['smtp_port'] ?: 'Not set'; ?></li>
                                <li><strong>SMTP Username:</strong> <?php echo $mailStatus['smtp_username'] ?: 'Not set'; ?></li>
                                <li><strong>PHPMailer:</strong> 
                                    <span class="badge <?php echo $mailStatus['phpmailer_available'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $mailStatus['phpmailer_available'] ? 'Available' : 'Not Available'; ?>
                                    </span>
                                </li>
                                <li><strong>SMTP Password:</strong> 
                                    <span class="badge <?php echo $mailStatus['smtp_password_configured'] ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo $mailStatus['smtp_password_configured'] ? 'Configured' : 'Not Configured'; ?>
                                    </span>
                                </li>
                                <li><strong>Connection:</strong> 
                                    <span class="badge <?php echo $mailStatus['can_connect'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $mailStatus['can_connect'] ? 'Connected' : 'Not Connected'; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Recommendations:</h6>
                            <?php if (!empty($mailStatus['recommendations'])): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($mailStatus['recommendations'] as $recommendation): ?>
                                        <li><i class="fas fa-info-circle text-info me-2"></i><?php echo htmlspecialchars($recommendation); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-success"><i class="fas fa-check-circle me-2"></i>Mail server is properly configured!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!$mailStatus['can_connect']): ?>
                        <div class="alert alert-warning mt-3">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Mail Server Configuration Required</h6>
                            <p class="mb-2">To send newsletters, you need to configure your mail server. Here are your options:</p>
                            <ol>
                                <li><strong>Use Gmail SMTP:</strong> Update php.ini with SMTP=smtp.gmail.com, smtp_port=587</li>
                                <li><strong>Use Outlook SMTP:</strong> Update php.ini with SMTP=smtp-mail.outlook.com, smtp_port=587</li>
                                <li><strong>Install local mail server:</strong> Configure XAMPP's Mercury mail server</li>
                                <li><strong>Use external service:</strong> Services like SendGrid, Mailgun, or AWS SES</li>
                            </ol>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Newsletter Tips -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Newsletter Best Practices</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Content Tips:</h6>
                            <ul>
                                <li>Keep subject lines under 50 characters</li>
                                <li>Use engaging, action-oriented language</li>
                                <li>Include valuable content that educates or entertains</li>
                                <li>Add clear call-to-action buttons</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Technical Tips:</h6>
                            <ul>
                                <li>Test emails before sending to all subscribers</li>
                                <li>Use HTML formatting for better readability</li>
                                <li>Include unsubscribe links (automatically added)</li>
                                <li>Monitor delivery rates and engagement</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Auto-resize textarea
document.getElementById('content').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Template preview update
document.getElementById('template').addEventListener('change', function() {
    const template = this.value;
    const previewContent = document.querySelector('.preview-content');
    
    let description = '';
    switch(template) {
        case 'default':
            description = '<strong>Default Template:</strong> Professional design with TelieAcademy branding, gradient header, and call-to-action buttons.';
            break;
        case 'minimal':
            description = '<strong>Minimal Template:</strong> Clean, simple design focused on content readability.';
            break;
        case 'premium':
            description = '<strong>Premium Template:</strong> Exclusive design with premium branding and special features for premium members.';
            break;
    }
    
    previewContent.innerHTML = description;
});
</script>

<?php include '../includes/footer.php'; ?>
