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
            
            // Clear all session data
            session_unset();
            
            // Destroy the session
            session_destroy();
            
            return ['success' => true, 'message' => 'Logged out successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Logout failed: ' . $e->getMessage()];
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
            
            $query = "SELECT id, username, email, is_premium, created_at FROM " . $this->table . " WHERE id = :id";
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

    // Register new user
    public function register($username, $email, $password) {
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
            
            $query = "INSERT INTO " . $this->table . " (username, email, password_hash) VALUES (:username, :email, :password_hash)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $passwordHash);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'User registered successfully'];
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
}
?> 