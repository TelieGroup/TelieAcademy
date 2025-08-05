<?php
require_once dirname(__DIR__) . '/config/database.php';

class Tag {
    private $conn;
    private $table = 'tags';

    public function __construct() {
        $this->conn = getDB();
    }

    // Get all tags
    public function getAllTags() {
        $query = "SELECT t.*, COUNT(pt.post_id) as post_count
                  FROM " . $this->table . " t
                  LEFT JOIN post_tags pt ON t.id = pt.tag_id
                  LEFT JOIN posts p ON pt.post_id = p.id AND p.status = 'published'
                  GROUP BY t.id
                  ORDER BY post_count DESC, t.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get popular tags
    public function getPopularTags($limit = 10) {
        $query = "SELECT t.*, COUNT(pt.post_id) as post_count
                  FROM " . $this->table . " t
                  LEFT JOIN post_tags pt ON t.id = pt.tag_id
                  LEFT JOIN posts p ON pt.post_id = p.id AND p.status = 'published'
                  GROUP BY t.id
                  HAVING post_count > 0
                  ORDER BY post_count DESC
                  LIMIT " . (int)$limit;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get tag by slug
    public function getTagBySlug($slug) {
        $query = "SELECT * FROM " . $this->table . " WHERE slug = :slug";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get tag with post count
    public function getTagWithPostCount($slug) {
        $query = "SELECT t.*, COUNT(pt.post_id) as post_count
                  FROM " . $this->table . " t
                  LEFT JOIN post_tags pt ON t.id = pt.tag_id
                  LEFT JOIN posts p ON pt.post_id = p.id AND p.status = 'published'
                  WHERE t.slug = :slug
                  GROUP BY t.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get tag by ID
    public function getTagById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Create new tag
    public function createTag($data) {
        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['name']);
            }

            $query = "INSERT INTO " . $this->table . " (name, slug, description, color, status, created_at) 
                      VALUES (:name, :slug, :description, :color, :status, CURRENT_TIMESTAMP)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':slug', $data['slug']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':color', $data['color']);
            $stmt->bindParam(':status', $data['status']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tag created successfully', 'id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create tag'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error creating tag: ' . $e->getMessage()];
        }
    }

    // Update tag
    public function updateTag($id, $data) {
        try {
            // Generate new slug if name changed
            $currentTag = $this->getTagById($id);
            if (!$currentTag) {
                return ['success' => false, 'message' => 'Tag not found'];
            }

            $slug = $currentTag['name'] !== $data['name'] ? $this->generateSlug($data['name']) : $currentTag['slug'];

            $query = "UPDATE " . $this->table . " 
                      SET name = :name, slug = :slug, description = :description, color = :color, status = :status, updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':color', $data['color']);
            $stmt->bindParam(':status', $data['status']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tag updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update tag'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating tag: ' . $e->getMessage()];
        }
    }

    // Delete tag
    public function deleteTag($id) {
        try {
            // Check if tag has posts
            $query = "SELECT COUNT(*) as count FROM post_tags WHERE tag_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete tag: It has ' . $result['count'] . ' posts associated with it'];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tag deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete tag'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting tag: ' . $e->getMessage()];
        }
    }

    // Get tag statistics
    public function getTagStatistics() {
        try {
            $stats = [];
            
            // Total tags
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_tags'] = $result['total'];
            
            // Most used tag
            $query = "SELECT t.name, COUNT(pt.post_id) as post_count
                      FROM " . $this->table . " t
                      LEFT JOIN post_tags pt ON t.id = pt.tag_id
                      LEFT JOIN posts p ON pt.post_id = p.id AND p.status = 'published'
                      GROUP BY t.id
                      HAVING post_count > 0
                      ORDER BY post_count DESC
                      LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['most_used'] = $result ? $result['name'] . ' (' . $result['post_count'] . ' posts)' : 'No tags used';
            
            // Recently added
            $query = "SELECT name, created_at FROM " . $this->table . " ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['recently_added'] = $result ? $result['name'] . ' (' . date('M j, Y', strtotime($result['created_at'])) . ')' : 'No tags';
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get all tags with colors and properties
    public function getAllTagsWithProperties() {
        try {
            $query = "SELECT t.*, COUNT(pt.post_id) as post_count
                      FROM " . $this->table . " t
                      LEFT JOIN post_tags pt ON t.id = pt.tag_id
                      LEFT JOIN posts p ON pt.post_id = p.id AND p.status = 'published'
                      GROUP BY t.id
                      ORDER BY t.name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get tag by name with properties
    public function getTagByName($name) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE name = :name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Generate slug from name
    private function generateSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}
?> 