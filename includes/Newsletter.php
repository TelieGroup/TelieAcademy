<?php
require_once dirname(__DIR__) . '/config/database.php';

class Newsletter {
    private $conn;
    private $table = 'newsletter_subscribers';

    public function __construct() {
        $this->conn = getDB();
    }

    // Subscribe to newsletter
    public function subscribe($email) {
        // Check if already subscribed
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already subscribed'];
        }

        // Add new subscriber
        $query = "INSERT INTO " . $this->table . " (email) VALUES (:email)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Successfully subscribed to newsletter'];
        } else {
            return ['success' => false, 'message' => 'Failed to subscribe'];
        }
    }

    // Unsubscribe from newsletter
    public function unsubscribe($email) {
        $query = "UPDATE " . $this->table . " SET is_active = FALSE WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Successfully unsubscribed'];
        } else {
            return ['success' => false, 'message' => 'Failed to unsubscribe'];
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
}
?> 