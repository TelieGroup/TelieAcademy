<?php
require_once dirname(__DIR__) . '/config/database.php';

class Bookmark {
    private $conn;
    
    public function __construct() {
        $this->conn = getDB();
    }
    
    /**
     * Add a bookmark for a user and post
     */
    public function addBookmark($userId, $postId) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO bookmarks (user_id, post_id) 
                VALUES (:user_id, :post_id) 
                ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding bookmark: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a bookmark for a user and post
     */
    public function removeBookmark($userId, $postId) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM bookmarks WHERE user_id = :user_id AND post_id = :post_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error removing bookmark: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a user has bookmarked a specific post
     */
    public function isBookmarked($userId, $postId) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM bookmarks WHERE user_id = :user_id AND post_id = :post_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error checking bookmark status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all bookmarks for a user
     */
    public function getUserBookmarks($userId, $limit = 20, $offset = 0) {
        try {
            $stmt = $this->conn->prepare("
                SELECT b.*, p.title, p.slug, p.excerpt, p.featured_image, p.published_at,
                       c.name as category_name, c.slug as category_slug,
                       u.username as author_name
                FROM bookmarks b
                JOIN posts p ON b.post_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE b.user_id = :user_id AND p.status = 'published'
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting user bookmarks: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get bookmark count for a post
     */
    public function getPostBookmarkCount($postId) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM bookmarks WHERE post_id = :post_id");
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (Exception $e) {
            error_log("Error getting post bookmark count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get user's bookmark count
     */
    public function getUserBookmarkCount($userId) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM bookmarks WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (Exception $e) {
            error_log("Error getting user bookmark count: " . $e->getMessage());
            return 0;
        }
    }
}
?>
