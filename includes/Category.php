<?php
require_once dirname(__DIR__) . '/config/database.php';

class Category {
    private $conn;
    private $table = 'categories';

    public function __construct() {
        $this->conn = getDB();
    }

    // Get all categories
    public function getAllCategories() {
        $query = "SELECT c.*, COUNT(p.id) as post_count
                  FROM " . $this->table . " c
                  LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
                  GROUP BY c.id
                  ORDER BY c.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get category by slug
    public function getCategoryBySlug($slug) {
        $query = "SELECT * FROM " . $this->table . " WHERE slug = :slug";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get category with post count
    public function getCategoryWithPostCount($slug) {
        $query = "SELECT c.*, COUNT(p.id) as post_count
                  FROM " . $this->table . " c
                  LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
                  WHERE c.slug = :slug
                  GROUP BY c.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get category by ID
    public function getCategoryById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Create new category
    public function createCategory($data) {
        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['name']);
            }

            $query = "INSERT INTO " . $this->table . " (name, slug, description, status, created_at) 
                      VALUES (:name, :slug, :description, :status, CURRENT_TIMESTAMP)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':slug', $data['slug']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':status', $data['status']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Category created successfully', 'id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create category'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error creating category: ' . $e->getMessage()];
        }
    }

    // Update category
    public function updateCategory($id, $data) {
        try {
            // Generate new slug if name changed
            $currentCategory = $this->getCategoryById($id);
            if (!$currentCategory) {
                return ['success' => false, 'message' => 'Category not found'];
            }

            $slug = $currentCategory['name'] !== $data['name'] ? $this->generateSlug($data['name']) : $currentCategory['slug'];

            $query = "UPDATE " . $this->table . " 
                      SET name = :name, slug = :slug, description = :description, status = :status, updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':status', $data['status']);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Category updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update category'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()];
        }
    }

    // Delete category
    public function deleteCategory($id) {
        try {
            // Check if category has posts
            $query = "SELECT COUNT(*) as count FROM posts WHERE category_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete category: It has ' . $result['count'] . ' posts associated with it'];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Category deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete category'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()];
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