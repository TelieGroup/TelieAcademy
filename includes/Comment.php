<?php
require_once dirname(__DIR__) . '/config/database.php';

class Comment {
    private $conn;
    private $table = 'comments';

    public function __construct() {
        $this->conn = getDB();
    }

    // Get comments for a post
    public function getCommentsByPost($postId) {
        $query = "SELECT c.*, u.username, 
                         COALESCE(c.reply_count, 0) as reply_count
                  FROM " . $this->table . " c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE c.post_id = :post_id AND c.status = 'approved'
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $postId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Add a new comment
    public function addComment($postId, $content, $userId = null, $guestName = null, $guestEmail = null) {
        $query = "INSERT INTO " . $this->table . " 
                  (post_id, user_id, guest_name, guest_email, content, status) 
                  VALUES (:post_id, :user_id, :guest_name, :guest_email, :content, 'pending')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':guest_name', $guestName);
        $stmt->bindParam(':guest_email', $guestEmail);
        $stmt->bindParam(':content', $content);

        return $stmt->execute();
    }

    // Get comment count for a post
    public function getCommentCount($postId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE post_id = :post_id AND status = 'approved'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $postId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    // Get total comment count (for admin dashboard)
    public function getTotalCommentCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    // Get all comments for admin
    public function getAllCommentsForAdmin($limit = null, $offset = 0) {
        try {
            $query = "SELECT c.*, u.username, p.title as post_title, p.slug as post_slug
                      FROM " . $this->table . " c
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN posts p ON c.post_id = p.id
                      ORDER BY c.created_at DESC";
            
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

    // Get comment by ID
    public function getCommentById($commentId) {
        try {
            $query = "SELECT c.*, u.username, p.title as post_title, p.slug as post_slug
                      FROM " . $this->table . " c
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN posts p ON c.post_id = p.id
                      WHERE c.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $commentId);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Get comments by user
    public function getCommentsByUser($userId) {
        try {
            $query = "SELECT c.*, u.username, p.title as post_title, p.slug as post_slug
                      FROM " . $this->table . " c
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN posts p ON c.post_id = p.id
                      WHERE c.user_id = :user_id AND c.status = 'approved'
                      ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Update comment status
    public function updateCommentStatus($commentId, $status) {
        try {
            $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $commentId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Comment status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update comment status'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating comment status: ' . $e->getMessage()];
        }
    }

    // Delete comment
    public function deleteComment($commentId) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $commentId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Comment deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete comment'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting comment: ' . $e->getMessage()];
        }
    }

    // Get comments by status
    public function getCommentsByStatus($status) {
        try {
            $query = "SELECT c.*, u.username, p.title as post_title, p.slug as post_slug
                      FROM " . $this->table . " c
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN posts p ON c.post_id = p.id
                      WHERE c.status = :status
                      ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get comment statistics
    public function getCommentStatistics() {
        try {
            $stats = [];
            
            // Total comments
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_comments'] = $result['total'];
            
            // Pending comments
            $query = "SELECT COUNT(*) as pending FROM " . $this->table . " WHERE status = 'pending'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['pending_comments'] = $result['pending'];
            
            // Approved comments
            $query = "SELECT COUNT(*) as approved FROM " . $this->table . " WHERE status = 'approved'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['approved_comments'] = $result['approved'];
            
            // Recent comments
            $query = "SELECT c.content, u.username, c.created_at 
                      FROM " . $this->table . " c
                      LEFT JOIN users u ON c.user_id = u.id
                      ORDER BY c.created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['recent_comment'] = $result ? 
                ($result['username'] ?: 'Guest') . ' - ' . substr($result['content'], 0, 50) . '... (' . date('M j, Y', strtotime($result['created_at'])) . ')' : 
                'No comments';
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get count of pending comments for admin notification badge
     */
    public function getPendingCommentCount() {
        try {
            $query = "SELECT COUNT(*) as pending_count FROM " . $this->table . " WHERE status = 'pending'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)($result['pending_count'] ?? 0);
        } catch (Exception $e) {
            error_log("Error counting pending comments: " . $e->getMessage());
            return 0;
        }
    }
}
?> 