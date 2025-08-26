<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/email_config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/includes/Newsletter.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailHelper {
    private $conn;
    private $fromEmail;
    private $fromName;
    private $replyTo;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    
    public function __construct() {
        $this->conn = getDB();
        $this->fromEmail = FROM_EMAIL;
        $this->fromName = FROM_NAME;
        $this->replyTo = REPLY_TO_EMAIL;
        
        // Gmail SMTP Configuration
        $this->smtpHost = SMTP_HOST;
        $this->smtpPort = SMTP_PORT;
        $this->smtpUsername = SMTP_USERNAME;
        $this->smtpPassword = SMTP_PASSWORD;
        
        // Configure PHP mail settings for XAMPP
        $this->configureMailSettings();
    }
    
    /**
     * Configure mail settings for XAMPP
     */
    private function configureMailSettings() {
        // Set SMTP settings for XAMPP
        // ini_set('SMTP', 'localhost');
        // ini_set('smtp_port', '25');
        
        // Alternative: Use external SMTP server (uncomment and configure as needed)
        ini_set('SMTP', 'smtp.gmail.com');
        ini_set('smtp_port', '587');
        ini_set('sendmail_from', $this->fromEmail);
    }
    
    /**
     * Create and configure PHPMailer instance
     */
    private function createPHPMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtpPort;
            
            // Default settings
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addReplyTo($this->replyTo, $this->fromName);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            
            return $mail;
        } catch (PHPMailerException $e) {
            error_log("PHPMailer configuration error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Send a newsletter email to subscribers
     */
    public function sendNewsletter($subject, $content, $subscribers = null, $template = 'default') {
        try {
            // If no subscribers provided, get all active subscribers
            if ($subscribers === null) {
                // Use Newsletter class method instead of local method
                $newsletter = new Newsletter();
                $subscribers = $newsletter->getActiveSubscribers();
            }
            
            if (empty($subscribers)) {
                return ['success' => false, 'message' => 'No active subscribers found'];
            }
            
            $successCount = 0;
            $failedCount = 0;
            $failedEmails = [];
            
            foreach ($subscribers as $subscriber) {
                $emailSent = $this->sendSingleNewsletter($subscriber, $subject, $content, $template);
                
                if ($emailSent) {
                    $successCount++;
                    // Update last email sent timestamp
                    $this->updateLastEmailSent($subscriber['id']);
                    // Log email sent
                    $this->logEmailSent($subscriber['id'], $subject, 'newsletter');
                } else {
                    $failedCount++;
                    $failedEmails[] = $subscriber['email'];
                }
            }
            
            return [
                'success' => true,
                'message' => "Newsletter sent successfully to $successCount subscribers",
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'failed_emails' => $failedEmails
            ];
            
        } catch (Exception $e) {
            error_log("Newsletter sending error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error sending newsletter: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send a single newsletter email
     */
    private function sendSingleNewsletter($subscriber, $subject, $content, $template = 'default') {
        try {
            $to = $subscriber['email'];
            $name = $subscriber['name'] ?: 'Subscriber';
            
            // Prepare email content
            $emailContent = $this->prepareEmailContent($subscriber, $subject, $content, $template);
            
            // Check if SMTP password is configured
            if (empty($this->smtpPassword)) {
                error_log("SMTP password not configured for newsletter to: $to");
                return false;
            }
            
            // Use PHPMailer for sending
            $mail = $this->createPHPMailer();
            if (!$mail) {
                error_log("Failed to initialize PHPMailer for newsletter to: $to");
                return false;
            }
            
            try {
                $mail->addAddress($to, $name);
                $mail->Subject = $subject;
                $mail->Body = $emailContent;
                
                // Add unsubscribe headers
                $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . $this->replyTo . '?subject=unsubscribe>');
                $mail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                
                $sent = $mail->send();
                
                if ($sent) {
                    error_log("Newsletter sent successfully to: $to");
                    return true;
                } else {
                    error_log("Failed to send newsletter to: $to");
                    return false;
                }
                
            } catch (PHPMailerException $e) {
                error_log("PHPMailer error sending newsletter to {$subscriber['email']}: " . $e->getMessage());
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error sending newsletter to {$subscriber['email']}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Prepare email content with template
     */
    private function prepareEmailContent($subscriber, $subject, $content, $template = 'default') {
        $unsubscribeLink = $this->generateUnsubscribeLink($subscriber['email']);
        
        switch ($template) {
            case 'default':
                return $this->getDefaultTemplate($subscriber, $subject, $content, $unsubscribeLink);
            case 'minimal':
                return $this->getMinimalTemplate($subscriber, $subject, $content, $unsubscribeLink);
            case 'premium':
                return $this->getPremiumTemplate($subscriber, $subject, $content, $unsubscribeLink);
            default:
                return $this->getDefaultTemplate($subscriber, $subject, $content, $unsubscribeLink);
        }
    }
    
    /**
     * Default newsletter template
     */
    private function getDefaultTemplate($subscriber, $subject, $content, $unsubscribeLink) {
        $name = $subscriber['name'] ?: 'Subscriber';
        
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($subject) . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .greeting { font-size: 18px; margin-bottom: 20px; }
                .newsletter-content { background: white; padding: 25px; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 14px; }
                .unsubscribe { margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; }
                .unsubscribe a { color: #6c757d; text-decoration: none; }
                .unsubscribe a:hover { text-decoration: underline; }
                .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
                .btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><i class="fas fa-graduation-cap"></i> TelieAcademy</h1>
                    <p>Your Gateway to Tech Excellence</p>
                </div>
                
                <div class="content">
                    <div class="greeting">
                        Hello ' . htmlspecialchars($name) . ',
                    </div>
                    
                    <div class="newsletter-content">
                        ' . $content . '
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $_SERVER['HTTP_HOST'] . 'TelieAcademy/courses.php" class="btn">Explore Courses</a>
                        <a href="' . $_SERVER['HTTP_HOST'] . 'TelieAcademy/posts.php" class="btn">Read Tutorials</a>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Thank you for being part of our learning community!</p>
                    <div class="unsubscribe">
                        <a href="' . $unsubscribeLink . '">Unsubscribe from this newsletter</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Minimal newsletter template
     */
    private function getMinimalTemplate($subscriber, $subject, $content, $unsubscribeLink) {
        $name = $subscriber['name'] ?: 'Subscriber';
        
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($subject) . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; }
                .content { background: #f8f9fa; padding: 25px; border-radius: 8px; }
                .footer { text-align: center; margin-top: 20px; color: #6c757d; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="content">
                    <h2>' . htmlspecialchars($subject) . '</h2>
                    <p>Hello ' . htmlspecialchars($name) . ',</p>
                    ' . $content . '
                </div>
                
                <div class="footer">
                    <p>TelieAcademy Newsletter</p>
                    <a href="' . $unsubscribeLink . '">Unsubscribe</a>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Premium newsletter template
     */
    private function getPremiumTemplate($subscriber, $subject, $content, $unsubscribeLink) {
        $name = $subscriber['name'] ?: 'Subscriber';
        
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($subject) . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; }
                .header { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { margin: 0; font-size: 28px; }
                .premium-badge { background: #ffc107; color: #212529; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .greeting { font-size: 18px; margin-bottom: 20px; }
                .newsletter-content { background: white; padding: 25px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107; }
                .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 14px; }
                .unsubscribe { margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; }
                .unsubscribe a { color: #6c757d; text-decoration: none; }
                .btn { display: inline-block; padding: 12px 24px; background: #ffc107; color: #212529; text-decoration: none; border-radius: 5px; margin: 10px 5px; font-weight: bold; }
                .btn:hover { background: #e0a800; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><i class="fas fa-crown"></i> TelieAcademy Premium</h1>
                    <p>Exclusive Content for Premium Members</p>
                    <span class="premium-badge">PREMIUM</span>
                </div>
                
                <div class="content">
                    <div class="greeting">
                        Hello ' . htmlspecialchars($name) . ',
                    </div>
                    
                    <div class="newsletter-content">
                        ' . $content . '
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $_SERVER['HTTP_HOST'] . 'TelieAcademy/courses.php" class="btn">Access Premium Courses</a>
                        <a href="' . $_SERVER['HTTP_HOST'] . 'TelieAcademy/subscription-settings.php" class="btn">Manage Subscription</a>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Thank you for being a Premium member!</p>
                    <div class="unsubscribe">
                        <a href="' . $unsubscribeLink . '">Unsubscribe from this newsletter</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Generate unsubscribe link
     */
    private function generateUnsubscribeLink($email) {
        $token = bin2hex(random_bytes(32));
        $this->storeUnsubscribeToken($email, $token);
        
        // Build the full URL with protocol and path
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['REQUEST_URI']);
        
        // If we're in the admin directory, go up one level
        if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
            $path = dirname(dirname($_SERVER['REQUEST_URI']));
        }
        
        // Ensure we have the correct path
        if ($path === '/' || $path === '\\') {
            $path = '';
        }
        
        return $protocol . $host . $path . '/unsubscribe.php?email=' . urlencode($email) . '&token=' . $token;
    }
    
    /**
     * Store unsubscribe token
     */
    private function storeUnsubscribeToken($email, $token) {
        try {
            $query = "UPDATE newsletter_subscribers SET unsubscribe_token = :token WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error storing unsubscribe token: " . $e->getMessage());
        }
    }
    
    /**
     * Get active subscribers
     */
    private function getActiveSubscribers() {
        try {
            $query = "SELECT id, email, name, subscription_type, preferences FROM newsletter_subscribers WHERE is_active = TRUE AND verified_at IS NOT NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting active subscribers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update last email sent timestamp
     */
    private function updateLastEmailSent($subscriberId) {
        try {
            $query = "UPDATE newsletter_subscribers SET last_email_sent = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $subscriberId);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating last email sent: " . $e->getMessage());
        }
    }
    
    /**
     * Log email sent
     */
    private function logEmailSent($subscriberId, $subject, $type) {
        try {
            $query = "INSERT INTO email_logs (subscriber_id, subject, type, sent_at) VALUES (:subscriber_id, :subject, :type, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':subscriber_id', $subscriberId);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error logging email: " . $e->getMessage());
        }
    }
    
    /**
     * Send a single email (for unsubscribe confirmations, etc.)
     */
    public function sendSingleEmail($toEmail, $subject, $content) {
        try {
            // Check if SMTP password is configured
            if (empty($this->smtpPassword)) {
                error_log("SMTP password not configured for email to: $toEmail");
                return false;
            }
            
            // Use PHPMailer for sending
            $mail = $this->createPHPMailer();
            if (!$mail) {
                error_log("Failed to initialize PHPMailer for email to: $toEmail");
                return false;
            }
            
            try {
                $mail->addAddress($toEmail);
                $mail->Subject = $subject;
                $mail->Body = $content;
                
                $sent = $mail->send();
                
                if ($sent) {
                    error_log("Email sent successfully to: $toEmail");
                    return true;
                } else {
                    error_log("Failed to send email to: $toEmail");
                    return false;
                }
                
            } catch (PHPMailerException $e) {
                error_log("PHPMailer error sending email to $toEmail: " . $e->getMessage());
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error sending email to $toEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test email functionality
     */
    public function testEmail($toEmail, $subject = 'Test Email', $content = 'This is a test email from TelieAcademy newsletter system.') {
        try {
            // Check if SMTP password is configured
            if (empty($this->smtpPassword)) {
                return ['success' => false, 'message' => 'SMTP password not configured. Please set the Gmail app password in EmailHelper.php'];
            }
            
            $testSubscriber = [
                'id' => 0,
                'email' => $toEmail,
                'name' => 'Test User',
                'subscription_type' => 'newsletter',
                'preferences' => '{}'
            ];
            
            $emailContent = $this->prepareEmailContent($testSubscriber, $subject, $content, 'default');
            
            // Use PHPMailer for sending
            $mail = $this->createPHPMailer();
            if (!$mail) {
                return ['success' => false, 'message' => 'Failed to initialize PHPMailer'];
            }
            
            try {
                $mail->addAddress($toEmail);
                $mail->Subject = $subject;
                $mail->Body = $emailContent;
                
                $sent = $mail->send();
                
                if ($sent) {
                    return ['success' => true, 'message' => 'Test email sent successfully to ' . $toEmail];
                } else {
                    return ['success' => false, 'message' => 'Failed to send test email'];
                }
                
            } catch (PHPMailerException $e) {
                return ['success' => false, 'message' => 'PHPMailer error: ' . $e->getMessage()];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error sending test email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Check mail server status and provide configuration guidance
     */
    public function getMailServerStatus() {
        $status = [
            'mail_function_available' => function_exists('mail'),
            'phpmailer_available' => class_exists('PHPMailer\PHPMailer\PHPMailer'),
            'smtp_host' => $this->smtpHost,
            'smtp_port' => $this->smtpPort,
            'smtp_username' => $this->smtpUsername,
            'smtp_password_configured' => !empty($this->smtpPassword),
            'sendmail_from' => ini_get('sendmail_from')
        ];
        
        // Check if we can connect to the mail server
        $canConnect = false;
        if ($status['smtp_host'] && $status['smtp_port']) {
            $connection = @fsockopen($status['smtp_host'], $status['smtp_port'], $errno, $errstr, 5);
            if ($connection) {
                $canConnect = true;
                fclose($connection);
            }
        }
        
        $status['can_connect'] = $canConnect;
        
        // Provide configuration recommendations
        $status['recommendations'] = [];
        
        if (!$status['phpmailer_available']) {
            $status['recommendations'][] = 'PHPMailer is not available. Run: composer require phpmailer/phpmailer';
        }
        
        if (!$status['smtp_password_configured']) {
            $status['recommendations'][] = 'Gmail app password not configured. Set smtpPassword in EmailHelper.php';
            $status['recommendations'][] = 'To get Gmail app password: Google Account > Security > 2-Step Verification > App passwords';
        }
        
        if (!$status['can_connect']) {
            $status['recommendations'][] = 'Cannot connect to Gmail SMTP server. Check internet connection and firewall settings.';
        }
        
        if ($status['phpmailer_available'] && $status['smtp_password_configured'] && $status['can_connect']) {
            $status['recommendations'][] = 'Mail server is properly configured with PHPMailer and Gmail SMTP!';
        }
        
        return $status;
    }
}
