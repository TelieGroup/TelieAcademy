<?php
require_once dirname(__DIR__) . '/config/database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        try {
            $this->conn = getDB();
        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    // User login
    public function login($username, $password) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE username = :username OR email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $username);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Session should already be started by config/session.php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Clear any existing session data
                session_unset();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_premium'] = $user['is_premium'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    // User logout
    public function logout() {
        try {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Store user info for logging before clearing session
            $userId = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['username'] ?? null;
            
            // Clear OAuth tokens if they exist
            if ($userId) {
                $this->clearOAuthTokens($userId);
            }
            
            // Clear OAuth state from session
            unset($_SESSION['oauth_state']);
            
            // Clear all session data
            session_unset();
            
            // Destroy the session
            session_destroy();
            
            // Clear session cookie if it exists
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            // Log the logout action
            if ($userId && $username) {
                error_log("User logout: ID=$userId, Username=$username, IP=" . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            }
            
            return ['success' => true, 'message' => 'Logged out successfully'];
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Logout failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Clear OAuth tokens for a user
     */
    private function clearOAuthTokens($userId) {
        try {
            $query = "DELETE FROM oauth_tokens WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to clear OAuth tokens for user $userId: " . $e->getMessage());
        }
    }

    // Check if user is logged in
    public function isLoggedIn() {
        try {
            // Session should already be started by config/session.php
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        } catch (Exception $e) {
            return false;
        }
    }

    // Check if user is premium
    public function isPremium() {
        try {
            // Session should already be started by config/session.php
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            return isset($_SESSION['is_premium']) && $_SESSION['is_premium'] && $this->isLoggedIn();
        } catch (Exception $e) {
            return false;
        }
    }

    // Check if user is admin
    public function isAdmin() {
        try {
            // Session should already be started by config/session.php
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] && $this->isLoggedIn();
        } catch (Exception $e) {
            return false;
        }
    }

    // Get current user
    public function getCurrentUser() {
        try {
            // Session should already be started by config/session.php
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                return null;
            }
            
            $query = "SELECT id, username, email, is_premium, is_admin, created_at, profile_picture FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            // If user doesn't exist in database, clear session
            if (!$user) {
                session_unset();
                session_destroy();
                return null;
            }
            
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }

    // Register new user with email verification
    public function register($username, $email, $password, $firstName = null, $lastName = null) {
        try {
            // Check if username or email already exists
            $query = "SELECT id FROM " . $this->table . " WHERE username = :username OR email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password and create user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $verificationToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + EMAIL_VERIFICATION_EXPIRY);
            
            $query = "INSERT INTO " . $this->table . " (username, email, password_hash, oauth_provider, 
                     first_name, last_name, email_verification_token, email_verification_expires, 
                     email_verified, is_active) 
                     VALUES (:username, :email, :password_hash, 'email', :first_name, :last_name, 
                     :verification_token, :expires_at, FALSE, TRUE)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':verification_token', $verificationToken);
            $stmt->bindParam(':expires_at', $expiresAt);
            
            if ($stmt->execute()) {
                $userId = $this->conn->lastInsertId();
                
                // Log verification attempt
                $this->logEmailVerification($userId, $email, $verificationToken, $expiresAt);
                
                // Send verification email
                $this->sendVerificationEmail($email, $username, $verificationToken);
                
                return [
                    'success' => true, 
                    'message' => 'Registration successful! Please check your email to verify your account.',
                    'user_id' => $userId
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to register user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    // Upgrade user to premium
    public function upgradeToPremium($userId) {
        try {
            $query = "UPDATE " . $this->table . " SET is_premium = TRUE WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId);
            
            if ($stmt->execute()) {
                // Update session - session should already be started by config/session.php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['is_premium'] = true;
                
                return ['success' => true, 'message' => 'Upgraded to premium successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to upgrade to premium'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Upgrade failed: ' . $e->getMessage()];
        }
    }

    // Get user count
    public function getUserCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    // Get all users for admin
    public function getAllUsers() {
        try {
            $query = "SELECT id, username, email, is_premium, created_at, 
                             (SELECT COUNT(*) FROM posts WHERE author_id = users.id) as post_count
                      FROM " . $this->table . " 
                      ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get user by ID
    public function getUserById($userId) {
        try {
            $query = "SELECT id, username, email, is_premium, created_at FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Create user (admin)
    public function createUser($data) {
        try {
            // Check if username or email already exists
            $query = "SELECT id FROM " . $this->table . " WHERE username = :username OR email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $query = "INSERT INTO " . $this->table . " (username, email, password_hash, is_premium) 
                      VALUES (:username, :email, :password_hash, :is_premium)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':is_premium', $data['is_premium']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'User created successfully', 'id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error creating user: ' . $e->getMessage()];
        }
    }

    // Update user (admin)
    public function updateUser($userId, $data) {
        try {
            // Check if username or email already exists (excluding current user)
            $query = "SELECT id FROM " . $this->table . " WHERE (username = :username OR email = :email) AND id != :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            $query = "UPDATE " . $this->table . " 
                      SET username = :username, email = :email, is_premium = :is_premium 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':is_premium', $data['is_premium']);
            $stmt->bindParam(':id', $userId);
            
            if ($stmt->execute()) {
                // Update password if provided
                if (!empty($data['password'])) {
                    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                    $query = "UPDATE " . $this->table . " SET password_hash = :password_hash WHERE id = :id";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':password_hash', $passwordHash);
                    $stmt->bindParam(':id', $userId);
                    $stmt->execute();
                }
                
                return ['success' => true, 'message' => 'User updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()];
        }
    }

    // Delete user (admin)
    public function deleteUser($userId) {
        try {
            // Check if user has posts
            $query = "SELECT COUNT(*) as count FROM posts WHERE author_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete user: User has ' . $result['count'] . ' posts'];
            }
            
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'User deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()];
        }
    }

    // Get user statistics
    public function getUserStatistics() {
        try {
            $stats = [];
            
            // Total users
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_users'] = $result['total'];
            
            // Premium users
            $query = "SELECT COUNT(*) as premium FROM " . $this->table . " WHERE is_premium = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['premium_users'] = $result['premium'];
            
            // Recent registrations
            $query = "SELECT username, created_at FROM " . $this->table . " ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['recent_registration'] = $result ? $result['username'] . ' (' . date('M j, Y', strtotime($result['created_at'])) . ')' : 'No users';
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get user by username
    public function getUserByUsername($username) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Get user by email
    public function getUserByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Update user profile
    public function updateProfile($userId, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET username = :username, email = :email, bio = :bio, website = :website, updated_at = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':username', $data['username']);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':bio', $data['bio'] ?? '');
            $stmt->bindValue(':website', $data['website'] ?? '');
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()];
        }
    }

    // Update profile picture
    public function updateProfilePicture($userId, $profilePicturePath) {
        try {
            // First, get the current profile picture to delete it if it exists
            $query = "SELECT profile_picture, oauth_provider FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user && $user['profile_picture']) {
                // Check if the current profile picture is from OAuth provider (URL) or local file
                if ($this->isOAuthProfilePicture($user['profile_picture'])) {
                    // For OAuth profile pictures (URLs), we don't delete them as they're external
                    // Just log that we're replacing an OAuth profile picture
                    error_log("Replacing OAuth profile picture for user $userId: " . $user['profile_picture']);
                } else {
                    // For local files, delete the old file if it exists
                    if (!str_contains($user['profile_picture'], 'default')) {
                        $oldPicturePath = dirname(__DIR__) . '/' . $user['profile_picture'];
                        if (file_exists($oldPicturePath)) {
                            unlink($oldPicturePath);
                        }
                    }
                }
            }
            
            // Update the profile picture in database
            $query = "UPDATE " . $this->table . " 
                     SET profile_picture = :profile_picture, updated_at = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':profile_picture', $profilePicturePath);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Profile picture updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile picture'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating profile picture: ' . $e->getMessage()];
        }
    }
    
    /**
     * Check if a profile picture URL is from an OAuth provider
     */
    private function isOAuthProfilePicture($profilePicturePath) {
        // Check if it's a URL (starts with http or https)
        if (filter_var($profilePicturePath, FILTER_VALIDATE_URL)) {
            return true;
        }
        
        // Check if it contains OAuth provider domains
        $oauthDomains = [
            'googleusercontent.com',  // Google
            'githubusercontent.com',  // GitHub
            'licdn.com',             // LinkedIn
            'linkedin.com'           // LinkedIn
        ];
        
        foreach ($oauthDomains as $domain) {
            if (strpos($profilePicturePath, $domain) !== false) {
                return true;
            }
        }
        
        return false;
    }

    // Verify password
    public function verifyPassword($userId, $password) {
        try {
            $query = "SELECT password_hash FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();
            
            return $user && password_verify($password, $user['password_hash']);
        } catch (Exception $e) {
            return false;
        }
    }

    // Change password
    public function changePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $query = "UPDATE " . $this->table . " 
                     SET password_hash = :password_hash, updated_at = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':password_hash', $hashedPassword);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to change password'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error changing password: ' . $e->getMessage()];
        }
    }
    
    // Email verification methods
    public function verifyEmail($token) {
        try {
            $query = "SELECT id, email, username FROM " . $this->table . " 
                     WHERE email_verification_token = :token 
                     AND email_verification_expires > NOW() 
                     AND email_verified = FALSE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user) {
                // Mark email as verified
                $updateQuery = "UPDATE " . $this->table . " SET 
                              email_verified = TRUE, 
                              email_verification_token = NULL, 
                              email_verification_expires = NULL 
                              WHERE id = :id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(':id', $user['id']);
                
                if ($updateStmt->execute()) {
                    // Log verification
                    $this->logEmailVerificationSuccess($user['id'], $user['email']);
                    return ['success' => true, 'message' => 'Email verified successfully!'];
                }
            }
            
            return ['success' => false, 'message' => 'Invalid or expired verification token'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Email verification failed: ' . $e->getMessage()];
        }
    }
    
    public function resendVerificationEmail($email) {
        try {
            $query = "SELECT id, username, email_verified FROM " . $this->table . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            if ($user['email_verified']) {
                return ['success' => false, 'message' => 'Email is already verified'];
            }
            
            // Generate new verification token
            $verificationToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + EMAIL_VERIFICATION_EXPIRY);
            
            $updateQuery = "UPDATE " . $this->table . " SET 
                          email_verification_token = :token, 
                          email_verification_expires = :expires 
                          WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':token', $verificationToken);
            $updateStmt->bindParam(':expires', $expiresAt);
            $updateStmt->bindParam(':id', $user['id']);
            
            if ($updateStmt->execute()) {
                // Log verification attempt
                $this->logEmailVerification($user['id'], $email, $verificationToken, $expiresAt);
                
                // Send verification email
                $this->sendVerificationEmail($email, $user['username'], $verificationToken);
                
                return ['success' => true, 'message' => 'Verification email sent successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to send verification email'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to send verification email: ' . $e->getMessage()];
        }
    }
    
    private function logEmailVerification($userId, $email, $token, $expiresAt) {
        try {
            $query = "INSERT INTO email_verification_logs (user_id, email, token, expires_at) 
                     VALUES (:user_id, :email, :token, :expires_at)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->execute();
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log('Failed to log email verification: ' . $e->getMessage());
        }
    }
    
    private function logEmailVerificationSuccess($userId, $email) {
        try {
            $query = "UPDATE email_verification_logs SET verified_at = NOW() 
                     WHERE user_id = :user_id AND email = :email 
                     ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log('Failed to log email verification success: ' . $e->getMessage());
        }
    }
    
    private function sendVerificationEmail($email, $username, $token) {
        try {
            $verificationUrl = SITE_URL . '/auth/verify-email.php?token=' . $token;
            
            $subject = 'Verify your email address - ' . SITE_NAME;
            $message = "
            <html>
            <body>
                <h2>Welcome to " . SITE_NAME . "!</h2>
                <p>Hi $username,</p>
                <p>Thank you for registering with us. Please click the link below to verify your email address:</p>
                <p><a href='$verificationUrl'>Verify Email Address</a></p>
                <p>If the link doesn't work, copy and paste this URL into your browser:</p>
                <p>$verificationUrl</p>
                <p>This link will expire in 24 hours.</p>
                <p>Best regards,<br>The " . SITE_NAME . " Team</p>
            </body>
            </html>";
            
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
                'Reply-To: ' . SMTP_FROM_EMAIL,
                'X-Mailer: PHP/' . phpversion()
            ];
            
            // For now, just log the email (in production, use proper SMTP)
            error_log("Verification email would be sent to: $email");
            error_log("Verification URL: $verificationUrl");
            
            return true;
        } catch (Exception $e) {
            error_log('Failed to send verification email: ' . $e->getMessage());
            return false;
        }
    }
}
?> 