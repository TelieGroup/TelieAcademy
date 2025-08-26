<?php
require_once dirname(__DIR__) . '/config/database.php';

class Reply {
    private $conn;
    private $table = 'comment_replies';

    public function __construct() {
        $this->conn = getDB();
    }

    // Add a new reply to a comment
    public function addReply($commentId, $content, $userId = null, $guestName = null, $guestEmail = null) {
        try {
            $this->conn->beginTransaction();

            // Insert the reply
            $query = "INSERT INTO " . $this->table . " 
                      (comment_id, user_id, guest_name, guest_email, content, status) 
                      VALUES (:comment_id, :user_id, :guest_name, :guest_email, :content, 'pending')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':comment_id', $commentId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':guest_name', $guestName);
            $stmt->bindParam(':guest_email', $guestEmail);
            $stmt->bindParam(':content', $content);

            if (!$stmt->execute()) {
                throw new Exception('Failed to insert reply');
            }

            // Update the comment's reply count
            $updateQuery = "UPDATE comments SET reply_count = reply_count + 1 WHERE id = :comment_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':comment_id', $commentId);
            
            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update reply count');
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Reply added successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error adding reply: ' . $e->getMessage()];
        }
    }

    // Get replies for a specific comment
    public function getRepliesByComment($commentId) {
        try {
            $query = "SELECT r.*, u.username
                      FROM " . $this->table . " r
                      LEFT JOIN users u ON r.user_id = u.id
                      WHERE r.comment_id = :comment_id AND r.status = 'approved'
                      ORDER BY r.created_at ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':comment_id', $commentId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get reply count for a comment
    public function getReplyCount($commentId) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                      WHERE comment_id = :comment_id AND status = 'approved'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':comment_id', $commentId);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    // Get all replies for admin
    public function getAllRepliesForAdmin($limit = null, $offset = 0) {
        try {
            $query = "SELECT r.*, u.username, c.content as comment_content, p.title as post_title
                      FROM " . $this->table . " r
                      LEFT JOIN users u ON r.user_id = u.id
                      LEFT JOIN comments c ON r.comment_id = c.id
                      LEFT JOIN posts p ON c.post_id = p.id
                      ORDER BY r.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Update reply status
    public function updateReplyStatus($replyId, $status) {
        try {
            $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $replyId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Reply status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update reply status'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating reply status: ' . $e->getMessage()];
        }
    }

    // Delete reply
    public function deleteReply($replyId) {
        try {
            $this->conn->beginTransaction();

            // Get the comment ID before deleting the reply
            $getCommentQuery = "SELECT comment_id FROM " . $this->table . " WHERE id = :id";
            $getCommentStmt = $this->conn->prepare($getCommentQuery);
            $getCommentStmt->bindParam(':id', $replyId);
            $getCommentStmt->execute();
            $commentResult = $getCommentStmt->fetch();

            if (!$commentResult) {
                throw new Exception('Reply not found');
            }

            $commentId = $commentResult['comment_id'];

            // Delete the reply
            $deleteQuery = "DELETE FROM " . $this->table . " WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $replyId);
            
            if (!$deleteStmt->execute()) {
                throw new Exception('Failed to delete reply');
            }

            // Update the comment's reply count
            $updateQuery = "UPDATE comments SET reply_count = GREATEST(reply_count - 1, 0) WHERE id = :comment_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':comment_id', $commentId);
            
            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update reply count');
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Reply deleted successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error deleting reply: ' . $e->getMessage()];
        }
    }

    // Get replies by user
    public function getRepliesByUser($userId) {
        try {
            $query = "SELECT r.*, u.username, c.content as comment_content, p.title as post_title
                      FROM " . $this->table . " r
                      LEFT JOIN users u ON r.user_id = u.id
                      LEFT JOIN comments c ON r.comment_id = c.id
                      LEFT JOIN posts p ON c.post_id = p.id
                      WHERE r.user_id = :user_id AND r.status = 'approved'
                      ORDER BY r.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get reply statistics
    public function getReplyStatistics() {
        try {
            $stats = [];
            
            // Total replies
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_replies'] = $result['total'];
            
            // Pending replies
            $query = "SELECT COUNT(*) as pending FROM " . $this->table . " WHERE status = 'pending'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['pending_replies'] = $result['pending'];
            
            // Approved replies
            $query = "SELECT COUNT(*) as approved FROM " . $this->table . " WHERE status = 'approved'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['approved_replies'] = $result['approved'];
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get total reply count
    public function getTotalReplyCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get count of pending replies for admin notification badge
     */
    public function getPendingReplyCount() {
        try {
            $query = "SELECT COUNT(*) as pending_count FROM " . $this->table . " WHERE status = 'pending'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)($result['pending_count'] ?? 0);
        } catch (Exception $e) {
            error_log("Error counting pending replies: " . $e->getMessage());
            return 0;
        }
    }
}
?>

