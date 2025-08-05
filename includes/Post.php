<?php
require_once dirname(__DIR__) . '/config/database.php';

class Post {
    private $conn;
    private $table = 'posts';

    public function __construct() {
        $this->conn = getDB();
    }

    // Get all published posts
    public function getAllPosts($limit = null, $offset = 0, $isPremium = false) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                         u.username as author_name,
                         GROUP_CONCAT(t.name) as tags
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  LEFT JOIN post_tags pt ON p.id = pt.post_id
                  LEFT JOIN tags t ON pt.tag_id = t.id
                  WHERE p.status = 'published'";
        
        if (!$isPremium) {
            $query .= " AND p.is_premium = FALSE";
        }
        
        $query .= " GROUP BY p.id ORDER BY p.published_at DESC";
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get post by slug
    public function getPostBySlug($slug, $isPremium = false) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                         u.username as author_name,
                         GROUP_CONCAT(t.name) as tags
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  LEFT JOIN post_tags pt ON p.id = pt.post_id
                  LEFT JOIN tags t ON pt.tag_id = t.id
                  WHERE p.slug = :slug AND p.status = 'published'";
        
        if (!$isPremium) {
            $query .= " AND p.is_premium = FALSE";
        }
        
        $query .= " GROUP BY p.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get posts by category
    public function getPostsByCategory($categorySlug, $isPremium = false) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                         u.username as author_name,
                         GROUP_CONCAT(t.name) as tags
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  LEFT JOIN post_tags pt ON p.id = pt.post_id
                  LEFT JOIN tags t ON pt.tag_id = t.id
                  WHERE c.slug = :category_slug AND p.status = 'published'";
        
        if (!$isPremium) {
            $query .= " AND p.is_premium = FALSE";
        }
        
        $query .= " GROUP BY p.id ORDER BY p.published_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_slug', $categorySlug);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get posts by tag
    public function getPostsByTag($tagSlug, $isPremium = false) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                         u.username as author_name,
                         GROUP_CONCAT(t.name) as tags
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  LEFT JOIN post_tags pt ON p.id = pt.post_id
                  LEFT JOIN tags t ON pt.tag_id = t.id
                  WHERE t.slug = :tag_slug AND p.status = 'published'";
        
        if (!$isPremium) {
            $query .= " AND p.is_premium = FALSE";
        }
        
        $query .= " GROUP BY p.id ORDER BY p.published_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tag_slug', $tagSlug);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get featured posts
    public function getFeaturedPosts($limit = 3, $isPremium = false) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                         u.username as author_name,
                         GROUP_CONCAT(t.name) as tags
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  LEFT JOIN post_tags pt ON p.id = pt.post_id
                  LEFT JOIN tags t ON pt.tag_id = t.id
                  WHERE p.status = 'published' AND p.is_featured = TRUE";
        
        if (!$isPremium) {
            $query .= " AND p.is_premium = FALSE";
        }
        
        $query .= " GROUP BY p.id ORDER BY p.published_at DESC LIMIT " . (int)$limit;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Search posts
    public function searchPosts($searchTerm, $isPremium = false) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                         u.username as author_name,
                         GROUP_CONCAT(t.name) as tags
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  LEFT JOIN post_tags pt ON p.id = pt.post_id
                  LEFT JOIN tags t ON pt.tag_id = t.id
                  WHERE p.status = 'published' 
                  AND (p.title LIKE :search OR p.content LIKE :search OR p.excerpt LIKE :search)";
        
        if (!$isPremium) {
            $query .= " AND p.is_premium = FALSE";
        }
        
        $query .= " GROUP BY p.id ORDER BY p.published_at DESC";

        $searchTerm = '%' . $searchTerm . '%';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get post count
    public function getPostCount($isPremium = false) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status = 'published'";
        
        if (!$isPremium) {
            $query .= " AND is_premium = FALSE";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    // Create new post
    public function createPost($data) {
        try {
            // Generate slug from title
            $slug = $this->generateSlug($data['title']);
            
            $query = "INSERT INTO " . $this->table . " 
                     (title, slug, excerpt, content, category_id, status, is_premium, is_featured, author_id, published_at) 
                     VALUES (:title, :slug, :excerpt, :content, :category_id, :status, :is_premium, :is_featured, :author_id, :published_at)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':excerpt', $data['excerpt']);
            $stmt->bindParam(':content', $data['content']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':is_premium', $data['is_premium']);
            $stmt->bindParam(':is_featured', $data['is_featured']);
            $stmt->bindParam(':author_id', $data['author_id']);
            
            $publishedAt = $data['status'] === 'published' ? date('Y-m-d H:i:s') : null;
            $stmt->bindParam(':published_at', $publishedAt);
            
            if ($stmt->execute()) {
                $postId = $this->conn->lastInsertId();
                
                // Handle tags if provided
                if (!empty($data['tags'])) {
                    $this->addTagsToPost($postId, $data['tags']);
                }
                
                return ['success' => true, 'message' => 'Post created successfully', 'id' => $postId];
            } else {
                return ['success' => false, 'message' => 'Failed to create post'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error creating post: ' . $e->getMessage()];
        }
    }

    // Generate slug from title
    private function generateSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
        
        // Check if slug already exists
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }

    // Add tags to post
    private function addTagsToPost($postId, $tagsString) {
        error_log("addTagsToPost called - Post ID: $postId, Tags: $tagsString");
        $tags = array_map('trim', explode(',', $tagsString));
        
        foreach ($tags as $tagName) {
            if (empty($tagName)) continue;
            
            error_log("Processing tag: $tagName");
            
            // Check if tag exists, create if not
            $tagId = $this->getOrCreateTag($tagName);
            error_log("Tag ID for '$tagName': $tagId");
            
            // Add post-tag relationship
            $query = "INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            $stmt->bindParam(':tag_id', $tagId);
            $stmt->execute();
            error_log("Added post-tag relationship: Post ID $postId, Tag ID $tagId");
        }
    }

    // Get or create tag
    private function getOrCreateTag($tagName) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tagName)));
        $slug = trim($slug, '-');
        
        // Check if tag exists
        $query = "SELECT id FROM tags WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['id'];
        }
        
        // Create new tag
        $query = "INSERT INTO tags (name, slug) VALUES (:name, :slug)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $tagName);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        return $this->conn->lastInsertId();
    }

    // Delete post
    public function deletePost($postId) {
        try {
            // First delete related records (post_tags)
            $query = "DELETE FROM post_tags WHERE post_id = :post_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            $stmt->execute();
            
            // Then delete the post
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $postId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Post deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete post'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting post: ' . $e->getMessage()];
        }
    }

    // Get all posts for admin (includes drafts and all statuses)
    public function getAllPostsForAdmin($limit = null, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                         u.username as author_name,
                         GROUP_CONCAT(t.name) as tags
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  LEFT JOIN post_tags pt ON p.id = pt.post_id
                  LEFT JOIN tags t ON pt.tag_id = t.id
                  GROUP BY p.id ORDER BY p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Update post
    public function updatePost($postId, $data) {
        try {
            // Generate new slug if title changed
            $currentPost = $this->getPostById($postId);
            if (!$currentPost) {
                return ['success' => false, 'message' => 'Post not found'];
            }
            
            $slug = $currentPost['title'] !== $data['title'] ? $this->generateSlug($data['title']) : $currentPost['slug'];
            
            $query = "UPDATE " . $this->table . " 
                     SET title = :title, slug = :slug, excerpt = :excerpt, content = :content, 
                         category_id = :category_id, status = :status, is_premium = :is_premium, 
                         is_featured = :is_featured, published_at = :published_at, updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $postId);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':excerpt', $data['excerpt']);
            $stmt->bindParam(':content', $data['content']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':is_premium', $data['is_premium']);
            $stmt->bindParam(':is_featured', $data['is_featured']);
            
            $publishedAt = $data['status'] === 'published' ? date('Y-m-d H:i:s') : null;
            $stmt->bindParam(':published_at', $publishedAt);
            
            if ($stmt->execute()) {
                // Handle tags if provided
                if (isset($data['tags'])) {
                    error_log("Updating tags for post ID: $postId, Tags: " . $data['tags']);
                    
                    // First, remove all existing tags for this post
                    $query = "DELETE FROM post_tags WHERE post_id = :post_id";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':post_id', $postId);
                    $stmt->execute();
                    error_log("Removed existing tags for post ID: $postId");
                    
                    // Then add the new tags
                    if (!empty($data['tags'])) {
                        $this->addTagsToPost($postId, $data['tags']);
                        error_log("Added new tags for post ID: $postId");
                    }
                }
                
                return ['success' => true, 'message' => 'Post updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update post'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating post: ' . $e->getMessage()];
        }
    }

    // Get post by ID (for admin)
    public function getPostById($postId) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                         u.username as author_name,
                         GROUP_CONCAT(t.name) as tags
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  LEFT JOIN post_tags pt ON p.id = pt.post_id
                  LEFT JOIN tags t ON pt.tag_id = t.id
                  WHERE p.id = :id
                  GROUP BY p.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $postId);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?> 