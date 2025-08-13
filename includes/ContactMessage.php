<?php
require_once __DIR__ . '/../config/database.php';

class ContactMessage {
    private $db;
    private $messagesTable = 'contact_messages';
    private $repliesTable = 'contact_replies';
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create a new contact message
     */
    public function createMessage($data) {
        try {
            $query = "INSERT INTO {$this->messagesTable} 
                      (user_id, first_name, last_name, email, phone, subject, message, newsletter_subscribe) 
                      VALUES (:user_id, :first_name, :last_name, :email, :phone, :subject, :message, :newsletter_subscribe)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':subject', $data['subject']);
            $stmt->bindParam(':message', $data['message']);
            $stmt->bindParam(':newsletter_subscribe', $data['newsletter_subscribe']);
            
            if ($stmt->execute()) {
                $messageId = $this->db->lastInsertId();
                
                // Handle newsletter subscription if requested
                if ($data['newsletter_subscribe'] && !empty($data['user_id'])) {
                    $this->handleNewsletterSubscription($data['user_id'], $data['email']);
                }
                
                return $messageId;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creating contact message: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all contact messages with optional filters
     */
    public function getAllMessages($filters = [], $page = 1, $limit = 20) {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $whereClause .= " AND cm.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($filters['priority'])) {
                $whereClause .= " AND cm.priority = :priority";
                $params[':priority'] = $filters['priority'];
            }
            
            if (!empty($filters['subject'])) {
                $whereClause .= " AND cm.subject LIKE :subject";
                $params[':subject'] = '%' . $filters['subject'] . '%';
            }
            
            if (!empty($filters['email'])) {
                $whereClause .= " AND cm.email LIKE :email";
                $params[':email'] = '%' . $filters['email'] . '%';
            }
            
            // Calculate offset
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT cm.*, 
                             u.username as user_username,
                             u.profile_picture as user_profile_picture,
                             a.username as admin_username,
                             (SELECT COUNT(*) FROM {$this->repliesTable} WHERE message_id = cm.id) as reply_count
                      FROM {$this->messagesTable} cm
                      LEFT JOIN users u ON cm.user_id = u.id
                      LEFT JOIN users a ON cm.assigned_to = a.id
                      {$whereClause}
                      ORDER BY 
                          CASE cm.priority 
                              WHEN 'urgent' THEN 1 
                              WHEN 'high' THEN 2 
                              WHEN 'medium' THEN 3 
                              WHEN 'low' THEN 4 
                          END,
                          cm.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting contact messages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a single contact message with replies
     */
    public function getMessageById($messageId) {
        try {
            // Get message details
            $query = "SELECT cm.*, 
                             u.username as user_username,
                             u.profile_picture as user_profile_picture,
                             a.username as admin_username
                      FROM {$this->messagesTable} cm
                      LEFT JOIN users u ON cm.user_id = u.id
                      LEFT JOIN users a ON cm.assigned_to = a.id
                      WHERE cm.id = :message_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':message_id', $messageId);
            $stmt->execute();
            
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$message) {
                return null;
            }
            
            // Get replies
            $repliesQuery = "SELECT cr.*, u.username as admin_username
                            FROM {$this->repliesTable} cr
                            LEFT JOIN users u ON cr.admin_id = u.id
                            WHERE cr.message_id = :message_id
                            ORDER BY cr.created_at ASC";
            
            $repliesStmt = $this->db->prepare($repliesQuery);
            $repliesStmt->bindParam(':message_id', $messageId);
            $repliesStmt->execute();
            
            $message['replies'] = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $message;
            
        } catch (PDOException $e) {
            error_log("Error getting contact message: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add an admin reply to a message
     */
    public function addReply($messageId, $adminId, $replyMessage, $isInternal = false) {
        try {
            $this->db->beginTransaction();
            
            // Insert reply
            $query = "INSERT INTO {$this->repliesTable} 
                      (message_id, admin_id, reply_message, is_internal) 
                      VALUES (:message_id, :admin_id, :reply_message, :is_internal)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':message_id', $messageId);
            $stmt->bindParam(':admin_id', $adminId);
            $stmt->bindParam(':reply_message', $replyMessage);
            $stmt->bindParam(':is_internal', $isInternal, PDO::PARAM_BOOL);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert reply");
            }
            
            // Update message status
            $updateQuery = "UPDATE {$this->messagesTable} 
                           SET status = 'replied', 
                               assigned_to = :admin_id,
                               updated_at = CURRENT_TIMESTAMP
                           WHERE id = :message_id";
            
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':admin_id', $adminId);
            $updateStmt->bindParam(':message_id', $messageId);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Failed to update message status");
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error adding reply: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update message status
     */
    public function updateMessageStatus($messageId, $status, $adminId = null, $adminNotes = null) {
        try {
            $query = "UPDATE {$this->messagesTable} 
                      SET status = :status, 
                          assigned_to = :admin_id,
                          admin_notes = :admin_notes,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE id = :message_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':admin_id', $adminId);
            $stmt->bindParam(':admin_notes', $adminNotes);
            $stmt->bindParam(':message_id', $messageId);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error updating message status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update message priority
     */
    public function updateMessagePriority($messageId, $priority) {
        try {
            $query = "UPDATE {$this->messagesTable} 
                      SET priority = :priority, 
                          updated_at = CURRENT_TIMESTAMP
                      WHERE id = :message_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':priority', $priority);
            $stmt->bindParam(':message_id', $messageId);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error updating message priority: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get message statistics
     */
    public function getMessageStatistics() {
        try {
            $query = "SELECT 
                          COUNT(*) as total_messages,
                          SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_messages,
                          SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_messages,
                          SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_messages,
                          SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_messages,
                          SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_messages,
                          SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_messages,
                          SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as messages_today,
                          SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as messages_this_week
                      FROM {$this->messagesTable}";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting message statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get messages by user ID
     */
    public function getMessagesByUser($userId) {
        try {
            $query = "SELECT cm.*, 
                             (SELECT COUNT(*) FROM {$this->repliesTable} WHERE message_id = cm.id) as reply_count
                      FROM {$this->messagesTable} cm
                      WHERE cm.user_id = :user_id
                      ORDER BY cm.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting user messages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Handle newsletter subscription
     */
    private function handleNewsletterSubscription($userId, $email) {
        try {
            // Check if Newsletter class exists
            if (class_exists('Newsletter')) {
                $newsletter = new Newsletter();
                $newsletter->subscribeUser($userId, $email, 'weekly');
            }
        } catch (Exception $e) {
            error_log("Error handling newsletter subscription: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a message (admin only)
     */
    public function deleteMessage($messageId) {
        try {
            $query = "DELETE FROM {$this->messagesTable} WHERE id = :message_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':message_id', $messageId);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error deleting message: " . $e->getMessage());
            return false;
        }
    }
}
?>
