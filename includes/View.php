<?php
require_once dirname(__DIR__) . '/config/database.php';

class View {
    private $conn;
    private $table = 'post_views';

    public function __construct() {
        $this->conn = getDB();
    }

    // Record a view for a post
    public function recordView($postId, $ipAddress = null, $userAgent = null) {
        try {
            // Check if this IP has already viewed this post recently (within 24 hours)
            if ($ipAddress) {
                $checkQuery = "SELECT id FROM " . $this->table . " 
                              WHERE post_id = :post_id AND ip_address = :ip_address 
                              AND viewed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(':post_id', $postId);
                $checkStmt->bindParam(':ip_address', $ipAddress);
                $checkStmt->execute();
                
                if ($checkStmt->fetch()) {
                    // Already viewed recently, don't count again
                    return ['success' => true, 'message' => 'View already recorded recently'];
                }
            }

            $this->conn->beginTransaction();

            // Insert the view record
            $query = "INSERT INTO " . $this->table . " 
                      (post_id, ip_address, user_agent) 
                      VALUES (:post_id, :ip_address, :user_agent)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);

            if (!$stmt->execute()) {
                throw new Exception('Failed to record view');
            }

            // Update the post's view count
            $updateQuery = "UPDATE posts SET view_count = view_count + 1 WHERE id = :post_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':post_id', $postId);
            
            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update view count');
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'View recorded successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error recording view: ' . $e->getMessage()];
        }
    }

    // Get view count for a specific post
    public function getViewCount($postId) {
        try {
            $query = "SELECT view_count FROM posts WHERE id = :post_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? $result['view_count'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    // Get detailed view statistics for a post
    public function getPostViewStats($postId) {
        try {
            $stats = [];
            
            // Total views
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE post_id = :post_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_views'] = $result['total'];
            
            // Views today
            $query = "SELECT COUNT(*) as today FROM " . $this->table . " 
                      WHERE post_id = :post_id AND DATE(viewed_at) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['views_today'] = $result['today'];
            
            // Views this week
            $query = "SELECT COUNT(*) as week FROM " . $this->table . " 
                      WHERE post_id = :post_id AND viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['views_week'] = $result['week'];
            
            // Views this month
            $query = "SELECT COUNT(*) as month FROM " . $this->table . " 
                      WHERE post_id = :post_id AND viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['views_month'] = $result['month'];
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get all view statistics for admin
    public function getAllViewStats($limit = null, $offset = 0) {
        try {
            $query = "SELECT p.id, p.title, p.slug, p.view_count,
                             COUNT(v.id) as actual_views,
                             MAX(v.viewed_at) as last_viewed
                      FROM posts p
                      LEFT JOIN " . $this->table . " v ON p.id = v.post_id
                      GROUP BY p.id
                      ORDER BY p.view_count DESC";
            
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

    // Get overall view statistics
    public function getOverallViewStats() {
        try {
            $stats = [];
            
            // Total views across all posts
            $query = "SELECT SUM(view_count) as total_views FROM posts";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_views'] = $result['total_views'] ?? 0;
            
            // Total actual view records
            $query = "SELECT COUNT(*) as total_records FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_records'] = $result['total_records'];
            
            // Views today
            $query = "SELECT COUNT(*) as today FROM " . $this->table . " WHERE DATE(viewed_at) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['views_today'] = $result['today'];
            
            // Views this week
            $query = "SELECT COUNT(*) as week FROM " . $this->table . " 
                      WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['views_week'] = $result['week'];
            
            // Views this month
            $query = "SELECT COUNT(*) as month FROM " . $this->table . " 
                      WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['views_month'] = $result['month'];
            
            // Average views per post
            $query = "SELECT AVG(view_count) as avg_views FROM posts WHERE view_count > 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['avg_views_per_post'] = round($result['avg_views'] ?? 0, 1);
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get trending posts (most viewed in recent period)
    public function getTrendingPosts($days = 7, $limit = 10) {
        try {
            $query = "SELECT p.id, p.title, p.slug, p.view_count,
                             COUNT(v.id) as recent_views
                      FROM posts p
                      LEFT JOIN " . $this->table . " v ON p.id = v.post_id 
                      AND v.viewed_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                      GROUP BY p.id
                      ORDER BY recent_views DESC, p.view_count DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Clean up old view records (optional maintenance)
    public function cleanupOldViews($daysToKeep = 365) {
        try {
            $query = "DELETE FROM " . $this->table . " 
                      WHERE viewed_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':days', $daysToKeep, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Old views cleaned up successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to cleanup old views'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error cleaning up old views: ' . $e->getMessage()];
        }
    }

    // Get views by date range for analytics
    public function getViewsByDateRange($startDate, $endDate, $postId = null) {
        try {
            $query = "SELECT DATE(viewed_at) as date, COUNT(*) as views
                      FROM " . $this->table;
            
            $params = [];
            $whereConditions = [];
            
            if ($postId) {
                $whereConditions[] = "post_id = :post_id";
                $params[':post_id'] = $postId;
            }
            
            $whereConditions[] = "viewed_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
            
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            $query .= " GROUP BY DATE(viewed_at) ORDER BY date";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>

