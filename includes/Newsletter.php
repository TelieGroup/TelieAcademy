<?php
require_once dirname(__DIR__) . '/config/database.php';

class Newsletter {
    private $conn;
    private $table = 'newsletter_subscribers';

    public function __construct() {
        $this->conn = getDB();
        $this->ensureUnsubscribeFeedbackTable();
    }

    // Subscribe to newsletter with enhanced data and user authentication
    public function subscribe($email, $name = null, $preferences = null, $frequency = 'weekly', $source = 'website', $userId = null, $subscriptionType = 'newsletter') {
        try {
            // Validate subscription type
            if (!in_array($subscriptionType, ['newsletter', 'premium'])) {
                return ['success' => false, 'message' => 'Invalid subscription type'];
            }
            
            // Check if already subscribed
            $query = "SELECT id, is_active, subscription_type, user_id FROM " . $this->table . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            
            $existing = $stmt->fetch();
            if ($existing) {
                if ($existing['is_active']) {
                    // Check if upgrading from newsletter to premium
                    if ($existing['subscription_type'] === 'newsletter' && $subscriptionType === 'premium') {
                        return $this->upgradeSubscription($existing['id'], $subscriptionType, $userId, $preferences, $frequency);
                    }
                    return ['success' => false, 'message' => 'Email already subscribed to ' . $existing['subscription_type']];
                } else {
                    // Reactivate subscription
                    return $this->reactivateSubscription($email, $name, $preferences, $frequency, $userId, $subscriptionType);
                }
            }

            // Generate tokens
            $verificationToken = bin2hex(random_bytes(32));
            $unsubscribeToken = bin2hex(random_bytes(32));
            
            // Prepare preferences
            $preferencesJson = $preferences ? json_encode($preferences) : json_encode([
                'categories' => ['all'],
                'post_notifications' => true,
                'weekly_digest' => true,
                'new_tutorials' => true
            ]);

            // Set premium expiration for premium subscriptions
            $premiumExpiresAt = null;
            $premiumStartedAt = null;
            if ($subscriptionType === 'premium') {
                $premiumStartedAt = date('Y-m-d H:i:s');
                $premiumExpiresAt = date('Y-m-d H:i:s', strtotime('+1 month')); // Default 1 month
            }

            // Add new subscriber
            $query = "INSERT INTO " . $this->table . " 
                      (user_id, email, name, subscription_type, preferences, frequency, source, 
                       verification_token, unsubscribe_token, ip_address, user_agent, 
                       premium_started_at, premium_expires_at) 
                      VALUES (:user_id, :email, :name, :subscription_type, :preferences, :frequency, :source, 
                              :verification_token, :unsubscribe_token, :ip_address, :user_agent, 
                              :premium_started_at, :premium_expires_at)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':subscription_type', $subscriptionType);
            $stmt->bindValue(':preferences', $preferencesJson);
            $stmt->bindValue(':frequency', $frequency);
            $stmt->bindValue(':source', $source);
            $stmt->bindValue(':verification_token', $verificationToken);
            $stmt->bindValue(':unsubscribe_token', $unsubscribeToken);
            $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? null);
            $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? null);
            $stmt->bindValue(':premium_started_at', $premiumStartedAt);
            $stmt->bindValue(':premium_expires_at', $premiumExpiresAt);
            
            if ($stmt->execute()) {
                $subscriberId = $this->conn->lastInsertId();
                
                // Update user premium status if subscribing to premium
                if ($subscriptionType === 'premium' && $userId) {
                    $this->updateUserPremiumStatus($userId, true);
                }
                
                return [
                    'success' => true, 
                    'message' => 'Successfully subscribed to ' . $subscriptionType,
                    'subscriber_id' => $subscriberId,
                    'verification_token' => $verificationToken,
                    'subscription_type' => $subscriptionType
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to subscribe'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Reactivate subscription
    private function reactivateSubscription($email, $name = null, $preferences = null, $frequency = 'weekly', $userId = null, $subscriptionType = 'newsletter') {
        try {
            $updates = ['is_active = TRUE', 'updated_at = NOW()'];
            $params = [':email' => $email];
            
            if ($userId) {
                $updates[] = 'user_id = :user_id';
                $params[':user_id'] = $userId;
            }
            
            if ($name) {
                $updates[] = 'name = :name';
                $params[':name'] = $name;
            }
            
            if ($preferences) {
                $updates[] = 'preferences = :preferences';
                $params[':preferences'] = json_encode($preferences);
            }
            
            $updates[] = 'frequency = :frequency';
            $params[':frequency'] = $frequency;
            
            $updates[] = 'subscription_type = :subscription_type';
            $params[':subscription_type'] = $subscriptionType;
            
            // Set premium dates if premium subscription
            if ($subscriptionType === 'premium') {
                $updates[] = 'premium_started_at = NOW()';
                $updates[] = 'premium_expires_at = DATE_ADD(NOW(), INTERVAL 1 MONTH)';
            }
            
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $updates) . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($stmt->execute()) {
                // Update user premium status if reactivating premium subscription
                if ($subscriptionType === 'premium' && $userId) {
                    $this->updateUserPremiumStatus($userId, true);
                }
                
                return ['success' => true, 'message' => 'Successfully reactivated ' . $subscriptionType . ' subscription'];
            } else {
                return ['success' => false, 'message' => 'Failed to reactivate subscription'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Upgrade subscription from newsletter to premium
    private function upgradeSubscription($subscriberId, $subscriptionType, $userId = null, $preferences = null, $frequency = 'weekly') {
        try {
            $updates = ['subscription_type = :subscription_type', 'updated_at = NOW()'];
            $params = [':id' => $subscriberId, ':subscription_type' => $subscriptionType];
            
            if ($userId) {
                $updates[] = 'user_id = :user_id';
                $params[':user_id'] = $userId;
            }
            
            if ($preferences) {
                $updates[] = 'preferences = :preferences';
                $params[':preferences'] = json_encode($preferences);
            }
            
            $updates[] = 'frequency = :frequency';
            $params[':frequency'] = $frequency;
            
            // Set premium dates
            if ($subscriptionType === 'premium') {
                $updates[] = 'premium_started_at = NOW()';
                $updates[] = 'premium_expires_at = DATE_ADD(NOW(), INTERVAL 1 MONTH)';
            }
            
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($stmt->execute()) {
                // Update user premium status if upgrading to premium
                if ($subscriptionType === 'premium' && $userId) {
                    $this->updateUserPremiumStatus($userId, true);
                }
                
                return ['success' => true, 'message' => 'Successfully upgraded to ' . $subscriptionType . ' subscription'];
            } else {
                return ['success' => false, 'message' => 'Failed to upgrade subscription'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Update user premium status in users table
    private function updateUserPremiumStatus($userId, $isPremium) {
        try {
            $query = "UPDATE users SET is_premium = :is_premium, updated_at = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':is_premium', $isPremium ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Update session if this is the current user
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
                    $_SESSION['is_premium'] = $isPremium;
                    error_log("Updated session premium status for user $userId to " . ($isPremium ? 'true' : 'false'));
                }
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error updating user premium status: " . $e->getMessage());
            return false;
        }
    }

    // Check if user has valid premium subscription
    public function hasPremiumAccess($userId) {
        try {
            $query = "SELECT id, premium_expires_at FROM " . $this->table . " 
                      WHERE user_id = :user_id AND subscription_type = 'premium' AND is_active = TRUE 
                      AND (premium_expires_at IS NULL OR premium_expires_at > NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    // Get user's subscription info
    public function getUserSubscription($userId, $userEmail = null) {
        try {
            // First try to find by user_id
            $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id AND is_active = TRUE ORDER BY subscribed_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
            
            $subscription = $stmt->fetch();
            
            // If no subscription found by user_id and email is provided, try by email
            if (!$subscription && $userEmail) {
                $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND is_active = TRUE ORDER BY subscribed_at DESC LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':email', $userEmail);
                $stmt->execute();
                
                $subscription = $stmt->fetch();
                
                // If found by email but no user_id, update the record to link it to the user
                if ($subscription && is_null($subscription['user_id'])) {
                    $updateQuery = "UPDATE " . $this->table . " SET user_id = :user_id WHERE id = :subscription_id";
                    $updateStmt = $this->conn->prepare($updateQuery);
                    $updateStmt->bindValue(':user_id', $userId);
                    $updateStmt->bindValue(':subscription_id', $subscription['id']);
                    $updateStmt->execute();
                    
                    // Update the subscription array with the user_id
                    $subscription['user_id'] = $userId;
                }
            }
            
            if ($subscription && $subscription['preferences']) {
                $subscription['preferences'] = json_decode($subscription['preferences'], true);
            }
            
            return $subscription;
        } catch (Exception $e) {
            error_log("Error getting user subscription: " . $e->getMessage());
            return null;
        }
    }

    // Get subscription plans
    public function getSubscriptionPlans($type = null) {
        try {
            $query = "SELECT * FROM subscription_plans WHERE is_active = TRUE";
            $params = [];
            
            if ($type) {
                $query .= " AND type = :type";
                $params[':type'] = $type;
            }
            
            $query .= " ORDER BY sort_order ASC, price ASC";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $plans = $stmt->fetchAll();
            
            // Decode features JSON
            foreach ($plans as &$plan) {
                if ($plan['features']) {
                    $plan['features'] = json_decode($plan['features'], true);
                }
            }
            
            return $plans;
        } catch (Exception $e) {
            return [];
        }
    }

    // Unsubscribe from newsletter
    public function unsubscribe($email) {
        try {
            // Get subscription info before unsubscribing
            $query = "SELECT user_id, subscription_type FROM " . $this->table . " WHERE email = :email AND is_active = TRUE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $subscription = $stmt->fetch();
            
            // Unsubscribe
            $query = "UPDATE " . $this->table . " SET is_active = FALSE WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                // Remove premium status if unsubscribing from premium
                if ($subscription && $subscription['subscription_type'] === 'premium' && $subscription['user_id']) {
                    $this->updateUserPremiumStatus($subscription['user_id'], false);
                }
                
                return ['success' => true, 'message' => 'Successfully unsubscribed'];
            } else {
                return ['success' => false, 'message' => 'Failed to unsubscribe'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Send unsubscribe confirmation email
    public function sendUnsubscribeConfirmation($email, $confirmationToken, $reason = '', $feedback = '') {
        try {
            // Store the confirmation token and feedback temporarily
            $query = "UPDATE " . $this->table . " SET 
                      unsubscribe_confirmation_token = :token,
                      unsubscribe_reason = :reason,
                      unsubscribe_feedback = :feedback,
                      unsubscribe_requested_at = NOW()
                      WHERE email = :email";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $confirmationToken);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':feedback', $feedback);
            $stmt->bindParam(':email', $email);
            
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Failed to process unsubscribe request'];
            }
            
            // Send confirmation email using EmailHelper
            require_once dirname(__DIR__) . '/includes/EmailHelper.php';
            $emailHelper = new EmailHelper();
            
            $subject = 'Confirm Your Unsubscribe Request - TelieAcademy';
            $content = $this->generateUnsubscribeConfirmationEmail($email, $confirmationToken, $reason, $feedback);
            
            $result = $emailHelper->sendSingleEmail($email, $subject, $content);
            
            if ($result) {
                return ['success' => true, 'message' => 'Confirmation email sent successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to send confirmation email'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Generate unsubscribe confirmation email content
    private function generateUnsubscribeConfirmationEmail($email, $token, $reason, $feedback) {
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
        
        $confirmationLink = $protocol . $host . $path . '/confirm_unsubscribe?email=' . urlencode($email) . '&token=' . $token;
        
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirm Unsubscribe - TelieAcademy</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #f8f9fa; border-radius: 10px; padding: 30px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; margin: -30px -30px 30px -30px; }
                .content { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; }
                .button { display: inline-block; padding: 15px 30px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .button:hover { background: #c82333; }
                .footer { text-align: center; color: #6c757d; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Confirm Unsubscribe Request</h1>
                </div>
                
                <div class="content">
                    <h2>Hello,</h2>
                    <p>We received a request to unsubscribe you from the TelieAcademy newsletter.</p>
                    
                    <p><strong>To complete your unsubscribe request, please click the button below:</strong></p>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $confirmationLink . '" class="button">Confirm Unsubscribe</a>
                    </div>
                    
                    <p><strong>What happens next?</strong></p>
                    <ul>
                        <li>You will be removed from our newsletter list</li>
                        <li>You will no longer receive emails from us</li>
                        <li>You can resubscribe anytime using our signup form</li>
                    </ul>
                    
                    <p><strong>If you did not request this unsubscribe:</strong></p>
                    <ul>
                        <li>Simply ignore this email</li>
                        <li>Your subscription will remain active</li>
                        <li>You will continue to receive our newsletters</li>
                    </ul>
                </div>
                
                <div class="footer">
                    <p>This confirmation link will expire in 24 hours for security reasons.</p>
                    <p>Thank you for being part of our learning community!</p>
                </div>
            </div>
        </body>
        </html>';
    }

    // Clear unsubscribe confirmation data
    public function clearUnsubscribeConfirmation($email) {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      unsubscribe_confirmation_token = NULL,
                      unsubscribe_reason = NULL,
                      unsubscribe_feedback = NULL,
                      unsubscribe_requested_at = NULL
                      WHERE email = :email";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error clearing unsubscribe confirmation: " . $e->getMessage());
            return false;
        }
    }

    // Get all active subscribers
    public function getActiveSubscribers() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = TRUE ORDER BY subscribed_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get subscriber count
    public function getSubscriberCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE is_active = TRUE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    // Check if email is already subscribed
    public function isEmailSubscribed($email) {
        try {
            $query = "SELECT id, is_active FROM " . $this->table . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result ? $result['is_active'] : false;
        } catch (Exception $e) {
            return false;
        }
    }

    // Get subscriber by email
    public function getSubscriberByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Delete subscriber (admin)
    public function deleteSubscriber($subscriberId) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $subscriberId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Subscriber deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete subscriber'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting subscriber: ' . $e->getMessage()];
        }
    }

    // Get newsletter statistics
    public function getNewsletterStatistics() {
        try {
            $stats = [];
            
            // Total subscribers
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_subscribers'] = $result['total'];
            
            // Active subscribers
            $query = "SELECT COUNT(*) as active FROM " . $this->table . " WHERE is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['active_subscribers'] = $result['active'];
            
            // Recent subscriber
            $query = "SELECT email, subscribed_at FROM " . $this->table . " ORDER BY subscribed_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['recent_subscriber'] = $result ? $result['email'] . ' (' . date('M j, Y', strtotime($result['subscribed_at'])) . ')' : 'No subscribers';
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get subscriber by ID
    public function getSubscriberById($subscriberId) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $subscriberId);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Update subscriber status
    public function updateSubscriberStatus($subscriberId, $isActive) {
        try {
            $query = "UPDATE " . $this->table . " SET is_active = :is_active WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':is_active', $isActive);
            $stmt->bindParam(':id', $subscriberId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Subscriber status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update subscriber status'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating subscriber status: ' . $e->getMessage()];
        }
    }

    // Get subscribers with pagination
    public function getSubscribersWithPagination($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT * FROM " . $this->table . " ORDER BY subscribed_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Search subscribers
    public function searchSubscribers($searchTerm) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email LIKE :search ORDER BY subscribed_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', '%' . $searchTerm . '%');
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Export subscribers to CSV
    public function exportSubscribers() {
        try {
            $subscribers = $this->getActiveSubscribers();
            
            $csv = "Email,Subscribed Date,Status\n";
            foreach ($subscribers as $subscriber) {
                $status = $subscriber['is_active'] ? 'Active' : 'Inactive';
                $csv .= '"' . $subscriber['email'] . '","' . $subscriber['subscribed_at'] . '","' . $status . '"' . "\n";
            }
            
            return $csv;
        } catch (Exception $e) {
            return false;
        }
    }

    // Update subscriber preferences
    public function updatePreferences($email, $preferences, $frequency = null) {
        try {
            $updates = ['preferences = :preferences', 'updated_at = NOW()'];
            $params = [':email' => $email, ':preferences' => json_encode($preferences)];
            
            if ($frequency) {
                $updates[] = 'frequency = :frequency';
                $params[':frequency'] = $frequency;
            }
            
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $updates) . " WHERE email = :email AND is_active = TRUE";
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Preferences updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update preferences'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Verify email subscription
    public function verifyEmail($token) {
        try {
            $query = "UPDATE " . $this->table . " SET verified_at = NOW(), updated_at = NOW() WHERE verification_token = :token AND verified_at IS NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':token', $token);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Email verified successfully'];
            } else {
                return ['success' => false, 'message' => 'Invalid or expired verification token'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Unsubscribe by token
    public function unsubscribeByToken($token) {
        try {
            $query = "UPDATE " . $this->table . " SET is_active = FALSE, updated_at = NOW() WHERE unsubscribe_token = :token AND is_active = TRUE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':token', $token);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Successfully unsubscribed'];
            } else {
                return ['success' => false, 'message' => 'Invalid unsubscribe token or already unsubscribed'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Get subscriber preferences by email
    public function getSubscriberPreferences($email) {
        try {
            $query = "SELECT name, email, preferences, frequency, verified_at FROM " . $this->table . " WHERE email = :email AND is_active = TRUE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            
            $subscriber = $stmt->fetch();
            if ($subscriber) {
                $subscriber['preferences'] = json_decode($subscriber['preferences'], true);
                return ['success' => true, 'subscriber' => $subscriber];
            } else {
                return ['success' => false, 'message' => 'Subscriber not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Get subscribers by frequency
    public function getSubscribersByFrequency($frequency) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE frequency = :frequency AND is_active = TRUE ORDER BY subscribed_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':frequency', $frequency);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get verified subscribers only
    public function getVerifiedSubscribers() {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE is_active = TRUE AND verified_at IS NOT NULL ORDER BY subscribed_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Update last email sent timestamp
    public function updateLastEmailSent($subscriberId) {
        try {
            $query = "UPDATE " . $this->table . " SET last_email_sent = NOW(), updated_at = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $subscriberId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Get advanced analytics
    public function getAdvancedAnalytics() {
        try {
            $stats = [];
            
            // Subscription trends by month
            $query = "SELECT DATE_FORMAT(subscribed_at, '%Y-%m') as month, COUNT(*) as count 
                      FROM " . $this->table . " 
                      WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                      GROUP BY month ORDER BY month DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['monthly_subscriptions'] = $stmt->fetchAll();
            
            // Frequency distribution
            $query = "SELECT frequency, COUNT(*) as count 
                      FROM " . $this->table . " 
                      WHERE is_active = TRUE 
                      GROUP BY frequency";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['frequency_distribution'] = $stmt->fetchAll();
            
            // Source distribution
            $query = "SELECT source, COUNT(*) as count 
                      FROM " . $this->table . " 
                      GROUP BY source ORDER BY count DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['source_distribution'] = $stmt->fetchAll();
            
            // Verification rate
            $query = "SELECT 
                        COUNT(*) as total_subscribers,
                        COUNT(verified_at) as verified_subscribers,
                        ROUND((COUNT(verified_at) / COUNT(*)) * 100, 2) as verification_rate
                      FROM " . $this->table . " 
                      WHERE is_active = TRUE";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['verification_stats'] = $stmt->fetch();
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Sync premium status for expired subscriptions
    public function syncExpiredPremiumSubscriptions() {
        try {
            // Find users with expired premium subscriptions
            $query = "SELECT DISTINCT ns.user_id 
                      FROM " . $this->table . " ns
                      INNER JOIN users u ON ns.user_id = u.id
                      WHERE ns.subscription_type = 'premium' 
                      AND ns.is_active = TRUE 
                      AND ns.premium_expires_at IS NOT NULL 
                      AND ns.premium_expires_at < NOW()
                      AND u.is_premium = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $expiredUsers = $stmt->fetchAll();
            
            $count = 0;
            foreach ($expiredUsers as $user) {
                if ($this->updateUserPremiumStatus($user['user_id'], false)) {
                    $count++;
                }
            }
            
            return ['success' => true, 'expired_count' => $count];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error syncing expired subscriptions: ' . $e->getMessage()];
        }
    }

    // Check if user's premium subscription is still valid
    public function validateUserPremiumStatus($userId) {
        try {
            $hasPremium = $this->hasPremiumAccess($userId);
            
            // Get current user premium status from users table
            $query = "SELECT is_premium FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch();
            
            $currentIsPremium = $user ? (bool)$user['is_premium'] : false;
            
            // Sync if there's a mismatch
            if ($hasPremium !== $currentIsPremium) {
                $this->updateUserPremiumStatus($userId, $hasPremium);
                return ['synced' => true, 'is_premium' => $hasPremium];
            }
            
            return ['synced' => false, 'is_premium' => $currentIsPremium];
        } catch (Exception $e) {
            return ['synced' => false, 'is_premium' => false, 'error' => $e->getMessage()];
        }
    }

    // Get subscribers by subscription type
    public function getSubscribersByType($type) {
        try {
            $query = "SELECT id, email, name, subscription_type, preferences, frequency 
                      FROM " . $this->table . " 
                      WHERE subscription_type = :type AND is_active = TRUE AND verified_at IS NOT NULL
                      ORDER BY subscribed_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting subscribers by type: " . $e->getMessage());
            return [];
        }
    }

    // Ensure unsubscribe feedback table exists
    private function ensureUnsubscribeFeedbackTable() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS unsubscribe_feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                reason VARCHAR(100) NOT NULL,
                feedback TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_viewed BOOLEAN DEFAULT FALSE,
                viewed_at TIMESTAMP NULL,
                INDEX idx_email (email),
                INDEX idx_created_at (created_at),
                INDEX idx_is_viewed (is_viewed)
            )";
            $this->conn->exec($query);
            
            // Check if we need to add missing columns (for existing tables)
            $this->addMissingColumns();
        } catch (Exception $e) {
            error_log("Error ensuring unsubscribe feedback table: " . $e->getMessage());
        }
    }
    
    // Add missing columns for backward compatibility
    private function addMissingColumns() {
        try {
            // Check if unsubscribe_reason column exists
            $stmt = $this->conn->query("SHOW COLUMNS FROM unsubscribe_feedback LIKE 'unsubscribe_reason'");
            if ($stmt->rowCount() == 0) {
                $this->conn->exec("ALTER TABLE unsubscribe_feedback ADD COLUMN unsubscribe_reason VARCHAR(100) AFTER reason");
                $this->conn->exec("UPDATE unsubscribe_feedback SET unsubscribe_reason = reason WHERE unsubscribe_reason IS NULL");
            }
            
            // Check if unsubscribe_requested_at column exists
            $stmt = $this->conn->query("SHOW COLUMNS FROM unsubscribe_feedback LIKE 'unsubscribe_requested_at'");
            if ($stmt->rowCount() == 0) {
                $this->conn->exec("ALTER TABLE unsubscribe_feedback ADD COLUMN unsubscribe_requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER created_at");
                $this->conn->exec("UPDATE unsubscribe_feedback SET unsubscribe_requested_at = created_at WHERE unsubscribe_requested_at IS NULL");
            }
            
            // Check if is_viewed column exists
            $stmt = $this->conn->query("SHOW COLUMNS FROM unsubscribe_feedback LIKE 'is_viewed'");
            if ($stmt->rowCount() == 0) {
                $this->conn->exec("ALTER TABLE unsubscribe_feedback ADD COLUMN is_viewed BOOLEAN DEFAULT FALSE AFTER unsubscribe_requested_at");
                $this->conn->exec("ALTER TABLE unsubscribe_feedback ADD COLUMN viewed_at TIMESTAMP NULL AFTER is_viewed");
            }
        } catch (Exception $e) {
            error_log("Error adding missing columns: " . $e->getMessage());
        }
    }

    // Log unsubscribe feedback
    public function logUnsubscribeFeedback($email, $reason, $feedback) {
        try {
            $query = "INSERT INTO unsubscribe_feedback (email, reason, unsubscribe_reason, feedback, created_at, unsubscribe_requested_at, is_viewed) 
                      VALUES (:email, :reason, :unsubscribe_reason, :feedback, NOW(), NOW(), FALSE)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':unsubscribe_reason', $reason);
            $stmt->bindParam(':feedback', $feedback);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error logging unsubscribe feedback: " . $e->getMessage());
            return false;
        }
    }

    // Get all unsubscribe feedback for admin
    public function getUnsubscribeFeedback() {
        try {
            $query = "SELECT uf.id, uf.email, 
                             COALESCE(uf.unsubscribe_reason, uf.reason) as unsubscribe_reason,
                             uf.feedback as unsubscribe_feedback,
                             COALESCE(uf.unsubscribe_requested_at, uf.created_at) as unsubscribe_requested_at,
                             u.username 
                      FROM unsubscribe_feedback uf 
                      LEFT JOIN users u ON u.email = uf.email 
                      ORDER BY uf.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting unsubscribe feedback: " . $e->getMessage());
            return [];
        }
    }

    // Get count of unsubscribe feedback for admin
    public function getUnsubscribeFeedbackCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM unsubscribe_feedback";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting unsubscribe feedback count: " . $e->getMessage());
            return 0;
        }
    }

    // Get count of unviewed unsubscribe feedback for admin
    public function getUnviewedUnsubscribeFeedbackCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM unsubscribe_feedback WHERE is_viewed = FALSE";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting unviewed unsubscribe feedback count: " . $e->getMessage());
            return 0;
        }
    }

    // Mark all current feedback as viewed
    public function markAllFeedbackAsViewed() {
        try {
            $query = "UPDATE unsubscribe_feedback SET is_viewed = TRUE, viewed_at = NOW() WHERE is_viewed = FALSE";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $count = $stmt->rowCount();
            return ['success' => true, 'message' => "Marked {$count} feedback entries as viewed"];
        } catch (Exception $e) {
            error_log("Error marking feedback as viewed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error marking feedback as viewed: ' . $e->getMessage()];
        }
    }

    // Delete specific unsubscribe feedback
    public function deleteUnsubscribeFeedback($feedbackId) {
        try {
            $query = "DELETE FROM unsubscribe_feedback WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $feedbackId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Feedback deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Feedback not found'];
            }
        } catch (Exception $e) {
            error_log("Error deleting unsubscribe feedback: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting feedback: ' . $e->getMessage()];
        }
    }

    // Clear all unsubscribe feedback
    public function clearAllUnsubscribeFeedback() {
        try {
            $query = "DELETE FROM unsubscribe_feedback";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $count = $stmt->rowCount();
            return ['success' => true, 'message' => "Cleared {$count} feedback entries"];
        } catch (Exception $e) {
            error_log("Error clearing unsubscribe feedback: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error clearing feedback: ' . $e->getMessage()];
        }
    }

    // Get specific feedback by ID
    public function getFeedbackById($feedbackId) {
        try {
            $query = "SELECT uf.id, uf.email, 
                             COALESCE(uf.unsubscribe_reason, uf.reason) as unsubscribe_reason,
                             uf.feedback as unsubscribe_feedback,
                             COALESCE(uf.unsubscribe_requested_at, uf.created_at) as unsubscribe_requested_at,
                             u.username 
                      FROM unsubscribe_feedback uf 
                      LEFT JOIN users u ON u.email = uf.email 
                      WHERE uf.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $feedbackId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting feedback by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get count of pending newsletter subscriptions for admin notification badge
     */
    public function getPendingSubscriptionCount() {
        try {
            $query = "SELECT COUNT(*) as pending_count FROM " . $this->table . " WHERE status = 'pending'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)($result['pending_count'] ?? 0);
        } catch (Exception $e) {
            error_log("Error counting pending subscriptions: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total subscriber count for admin notification badge
     */
    public function getTotalSubscriberCount() {
        try {
            $query = "SELECT COUNT(*) as total_count FROM " . $this->table . " WHERE is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)($result['total_count'] ?? 0);
        } catch (Exception $e) {
            error_log("Error counting total subscribers: " . $e->getMessage());
            return 0;
        }
    }
}
?> 