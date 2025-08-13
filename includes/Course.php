<?php
require_once dirname(__DIR__) . '/config/database.php';

class Course {
    private $conn;
    private $coursesTable = 'courses';
    private $modulesTable = 'course_modules';
    private $materialsTable = 'course_materials';
    private $accessTable = 'user_material_access';

    public function __construct() {
        $this->conn = getDB();
    }

    // Course Management Methods
    public function getAllCourses($activeOnly = true) {
        try {
            $whereClause = $activeOnly ? "WHERE is_active = 1" : "";
            $query = "SELECT * FROM {$this->coursesTable} {$whereClause} ORDER BY title ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getCourseBySlug($slug) {
        try {
            $query = "SELECT * FROM {$this->coursesTable} WHERE slug = :slug AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':slug', $slug);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCourseById($id) {
        try {
            $query = "SELECT * FROM {$this->coursesTable} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    public function createCourse($title, $slug, $description, $thumbnail = null) {
        try {
            $query = "INSERT INTO {$this->coursesTable} (title, slug, description, thumbnail) 
                      VALUES (:title, :slug, :description, :thumbnail)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':thumbnail', $thumbnail);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Course creation error: " . $e->getMessage());
            return false;
        }
    }

    public function updateCourse($id, $title, $slug, $description, $thumbnail = null, $isActive = true) {
        try {
            $query = "UPDATE {$this->coursesTable} 
                      SET title = :title, slug = :slug, description = :description, 
                          thumbnail = :thumbnail, is_active = :is_active 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':thumbnail', $thumbnail);
            $stmt->bindParam(':is_active', $isActive);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteCourse($id) {
        try {
            $query = "DELETE FROM {$this->coursesTable} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Module Management Methods
    public function getModulesByCourse($courseId, $activeOnly = true) {
        try {
            $whereClause = $activeOnly ? "AND cm.is_active = 1" : "";
            $query = "SELECT cm.*, c.title as course_title 
                      FROM {$this->modulesTable} cm
                      JOIN {$this->coursesTable} c ON cm.course_id = c.id
                      WHERE cm.course_id = :course_id {$whereClause}
                      ORDER BY cm.order_index ASC, cm.title ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getModuleBySlug($courseId, $moduleSlug) {
        try {
            $query = "SELECT cm.*, c.title as course_title 
                      FROM {$this->modulesTable} cm
                      JOIN {$this->coursesTable} c ON cm.course_id = c.id
                      WHERE cm.course_id = :course_id AND cm.slug = :module_slug AND cm.is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->bindParam(':module_slug', $moduleSlug);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getModuleById($moduleId) {
        try {
            $query = "SELECT cm.*, c.title as course_title 
                      FROM {$this->modulesTable} cm
                      JOIN {$this->coursesTable} c ON cm.course_id = c.id
                      WHERE cm.id = :module_id AND cm.is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':module_id', $moduleId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

        public function createModule($courseId, $title, $slug, $description, $orderIndex = 0) {
        try {
            $query = "INSERT INTO {$this->modulesTable} (course_id, title, slug, description, order_index)
                      VALUES (:course_id, :title, :slug, :description, :order_index)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':order_index', $orderIndex);

            $result = $stmt->execute();

            if ($result) {
                return $this->conn->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Exception in createModule: " . $e->getMessage());
            return false;
        }
    }

    public function updateModule($id, $title, $slug, $description, $orderIndex, $isActive = true) {
        try {
            $query = "UPDATE {$this->modulesTable} 
                      SET title = :title, slug = :slug, description = :description, 
                          order_index = :order_index, is_active = :is_active 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':order_index', $orderIndex);
            $stmt->bindParam(':is_active', $isActive);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteModule($id) {
        try {
            $query = "DELETE FROM {$this->modulesTable} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Material Management Methods
    public function getMaterialsByModule($moduleId, $activeOnly = true) {
        try {
            // For now, don't filter by active status since materials table might not have is_active column
            $query = "SELECT cm.*, cm2.title as module_title, c.title as course_title 
                      FROM {$this->materialsTable} cm
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      JOIN {$this->coursesTable} c ON cm2.course_id = c.id
                      WHERE cm.module_id = :module_id
                      ORDER BY cm.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':module_id', $moduleId);
            $stmt->execute();
            
            // Debug logging
            error_log("getMaterialsByModule query executed for module_id: $moduleId");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Query result count: " . count($result));
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in getMaterialsByModule: " . $e->getMessage());
            return [];
        }
    }

    public function getMaterialById($id) {
        try {
            $query = "SELECT cm.*, cm2.title as module_title, c.title as course_title, 
                             cm2.course_id, cm.module_id
                      FROM {$this->materialsTable} cm
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      JOIN {$this->coursesTable} c ON cm2.course_id = c.id
                      WHERE cm.id = :id AND cm.is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function getMaterialByIdForAdmin($id) {
        try {
            $query = "SELECT cm.*, cm2.title as module_title, c.title as course_title, 
                             cm2.course_id, cm.module_id
                      FROM {$this->materialsTable} cm
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      JOIN {$this->coursesTable} c ON cm2.course_id = c.id
                      WHERE cm.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting material by ID for admin: " . $e->getMessage());
            return false;
        }
    }

    public function createMaterial($moduleId, $title, $description, $fileName, $filePath, $fileSize, $fileType, $coverImage = null, $coverImagePath = null) {
        try {
            $query = "INSERT INTO {$this->materialsTable} 
                      (module_id, title, description, file_name, file_path, file_size, file_type, cover_image, cover_image_path) 
                      VALUES (:module_id, :title, :description, :file_name, :file_path, :file_size, :file_type, :cover_image, :cover_image_path)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':module_id', $moduleId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':file_name', $fileName);
            $stmt->bindParam(':file_path', $filePath);
            $stmt->bindParam(':file_size', $fileSize);
            $stmt->bindParam(':file_type', $fileType);
            $stmt->bindParam(':cover_image', $coverImage);
            $stmt->bindParam(':cover_image_path', $coverImagePath);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateMaterial($id, $title, $description, $coverImage = null, $coverImagePath = null, $isActive = true) {
        try {
            $query = "UPDATE {$this->materialsTable} 
                      SET title = :title, description = :description, is_active = :is_active";
            $params = [':id' => $id, ':title' => $title, ':description' => $description, ':is_active' => $isActive];
            
            // Add cover image fields if provided
            if ($coverImage !== null && $coverImagePath !== null) {
                $query .= ", cover_image = :cover_image, cover_image_path = :cover_image_path";
                $params[':cover_image'] = $coverImage;
                $params[':cover_image_path'] = $coverImagePath;
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating material: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMaterial($id) {
        try {
            // Get file path before deletion
            $material = $this->getMaterialById($id);
            if ($material) {
                $filePath = $material['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $query = "DELETE FROM {$this->materialsTable} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function incrementDownloadCount($materialId) {
        try {
            $query = "UPDATE {$this->materialsTable} 
                      SET download_count = download_count + 1 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $materialId);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // User Access Methods
    public function recordUserAccess($userId, $materialId) {
        try {
            $this->conn->beginTransaction();
            
            // Check if access record exists
            $query = "SELECT * FROM {$this->accessTable} WHERE user_id = :user_id AND material_id = :material_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':material_id', $materialId);
            $stmt->execute();
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing record
                $query = "UPDATE {$this->accessTable} 
                          SET accessed_at = CURRENT_TIMESTAMP, download_count = download_count + 1, 
                              last_downloaded = CURRENT_TIMESTAMP
                          WHERE user_id = :user_id AND material_id = :material_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':material_id', $materialId);
            } else {
                // Create new record
                $query = "INSERT INTO {$this->accessTable} (user_id, material_id, download_count, last_downloaded) 
                          VALUES (:user_id, :material_id, 1, CURRENT_TIMESTAMP)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':material_id', $materialId);
            }
            
            $stmt->execute();
            
            // Increment global download count
            $this->incrementDownloadCount($materialId);
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getUserAccessHistory($userId) {
        try {
            $query = "SELECT ua.*, cm.title as material_title, cm.file_name, cm.file_type,
                             cm2.title as module_title, c.title as course_title
                      FROM {$this->accessTable} ua
                      JOIN {$this->materialsTable} cm ON ua.material_id = cm.id
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      JOIN {$this->coursesTable} c ON cm2.course_id = c.id
                      WHERE ua.user_id = :user_id
                      ORDER BY ua.last_downloaded DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Statistics Methods
    public function getCourseStatistics() {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT c.id) as total_courses,
                        COUNT(DISTINCT cm.id) as total_modules,
                        COUNT(DISTINCT cm2.id) as total_materials,
                        SUM(cm2.download_count) as total_downloads
                      FROM {$this->coursesTable} c
                      LEFT JOIN {$this->modulesTable} cm ON c.id = cm.course_id
                      LEFT JOIN {$this->materialsTable} cm2 ON cm.id = cm2.module_id
                      WHERE c.is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getPopularMaterials($limit = 10) {
        try {
            $query = "SELECT cm.*, cm2.title as module_title, c.title as course_title
                      FROM {$this->materialsTable} cm
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      JOIN {$this->coursesTable} c ON cm2.course_id = c.id
                      WHERE cm.is_active = 1
                      ORDER BY cm.download_count DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getDownloadStatistics() {
        try {
            $query = "SELECT 
                        SUM(cm.download_count) as total_downloads,
                        SUM(CASE WHEN DATE(cm.updated_at) = CURDATE() THEN 1 ELSE 0 END) as downloads_today,
                        SUM(CASE WHEN cm.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as downloads_week
                      FROM {$this->materialsTable} cm
                      WHERE cm.is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If no results, return default values
            if (!$result) {
                return [
                    'total_downloads' => 0,
                    'downloads_today' => 0,
                    'downloads_week' => 0
                ];
            }
            
            return [
                'total_downloads' => (int)($result['total_downloads'] ?? 0),
                'downloads_today' => (int)($result['downloads_today'] ?? 0),
                'downloads_week' => (int)($result['downloads_week'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log("Error getting download statistics: " . $e->getMessage());
            return [
                'total_downloads' => 0,
                'downloads_today' => 0,
                'downloads_week' => 0
            ];
        }
    }

    public function getRecentDownloads($days = 30) {
        try {
            $query = "SELECT 
                        ua.*, cm.title as material_title, cm.file_name, cm.file_type,
                        cm2.title as module_title, c.title as course_title,
                        u.username, u.email
                      FROM {$this->accessTable} ua
                      JOIN {$this->materialsTable} cm ON ua.material_id = cm.id
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      JOIN {$this->coursesTable} c ON cm2.course_id = c.id
                      JOIN {$this->userTable} u ON ua.user_id = u.id
                      WHERE ua.last_downloaded >= DATE_SUB(NOW(), INTERVAL :days DAY)
                      ORDER BY ua.last_downloaded DESC
                      LIMIT 100";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent downloads: " . $e->getMessage());
            return [];
        }
    }
}
?>
