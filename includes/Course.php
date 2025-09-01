<?php
require_once dirname(__DIR__) . '/config/database.php';

class Course {
    private $conn;
    private $coursesTable = 'courses';
    private $modulesTable = 'course_modules';
    private $materialsTable = 'course_materials';
    private $accessTable = 'user_material_access';
    private $progressTable = 'course_progress';
    private $enrollmentsTable = 'course_enrollments';
    private $postsTable = 'posts';

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
    public function getMaterialsByModule($moduleId, $activeOnly = true, $userId = null) {
        try {
            $whereClause = $activeOnly ? "AND cm.is_active = 1" : "";
            $query = "SELECT cm.*, cm2.title as module_title, c.title as course_title,
                             c.id as course_id,
                             " . ($userId ? "
                             CASE 
                                WHEN cm.required_lesson_id IS NOT NULL THEN 
                                    (SELECT CASE WHEN cp.progress_percentage >= 100 THEN 1 ELSE 0 END 
                                     FROM {$this->progressTable} cp 
                                     WHERE cp.user_id = :user_id AND cp.post_id = cm.required_lesson_id)
                                ELSE 1 
                             END as is_accessible,
                             uma.last_downloaded,
                             uma.download_count as user_download_count" : "
                             1 as is_accessible,
                             NULL as last_downloaded,
                             0 as user_download_count") . "
                      FROM {$this->materialsTable} cm
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      JOIN {$this->coursesTable} c ON cm2.course_id = c.id" .
                      ($userId ? " LEFT JOIN {$this->accessTable} uma ON uma.material_id = cm.id AND uma.user_id = :user_id" : "") . "
                      WHERE cm.module_id = :module_id {$whereClause}
                      ORDER BY cm.order_index ASC, cm.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':module_id', $moduleId);
            if ($userId) {
                $stmt->bindParam(':user_id', $userId);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getMaterialsByModule: " . $e->getMessage());
            return [];
        }
    }

    public function getMaterialsByLesson($postId, $userId = null) {
        try {
            $query = "SELECT cm.*, cm2.title as module_title, c.title as course_title,
                             c.id as course_id,
                             " . ($userId ? "
                             uma.last_downloaded,
                             uma.download_count as user_download_count" : "
                             NULL as last_downloaded,
                             0 as user_download_count") . "
                      FROM {$this->materialsTable} cm
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      JOIN {$this->coursesTable} c ON cm2.course_id = c.id" .
                      ($userId ? " LEFT JOIN {$this->accessTable} uma ON uma.material_id = cm.id AND uma.user_id = :user_id" : "") . "
                      WHERE cm.related_lesson_id = :post_id AND cm.is_active = 1
                      ORDER BY cm.order_index ASC, cm.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':post_id', $postId);
            if ($userId) {
                $stmt->bindParam(':user_id', $userId);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getMaterialsByLesson: " . $e->getMessage());
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

    public function createMaterial($moduleId, $title, $description, $fileName, $filePath, $fileSize, $fileType, $coverImage = null, $coverImagePath = null, $requiredLessonId = null, $relatedLessonId = null, $orderIndex = 0) {
        try {
            $query = "INSERT INTO {$this->materialsTable} 
                      (module_id, title, description, file_name, file_path, file_size, file_type, cover_image, cover_image_path, required_lesson_id, related_lesson_id, order_index) 
                      VALUES (:module_id, :title, :description, :file_name, :file_path, :file_size, :file_type, :cover_image, :cover_image_path, :required_lesson_id, :related_lesson_id, :order_index)";
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
            $stmt->bindParam(':required_lesson_id', $requiredLessonId);
            $stmt->bindParam(':related_lesson_id', $relatedLessonId);
            $stmt->bindParam(':order_index', $orderIndex);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error creating material: " . $e->getMessage());
            return false;
        }
    }

    public function trackMaterialAccess($userId, $materialId) {
        try {
            // Check if access record exists
            $query = "SELECT id, download_count FROM {$this->accessTable} 
                      WHERE user_id = :user_id AND material_id = :material_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':material_id', $materialId);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Update existing record
                $query = "UPDATE {$this->accessTable} 
                          SET download_count = download_count + 1, last_downloaded = CURRENT_TIMESTAMP
                          WHERE user_id = :user_id AND material_id = :material_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':material_id', $materialId);
                $stmt->execute();
            } else {
                // Create new access record
                $query = "INSERT INTO {$this->accessTable} (user_id, material_id, download_count)
                          VALUES (:user_id, :material_id, 1)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':material_id', $materialId);
                $stmt->execute();
            }
            
            // Update material download count
            $query = "UPDATE {$this->materialsTable} SET download_count = download_count + 1 WHERE id = :material_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':material_id', $materialId);
            $stmt->execute();
            
            return true;
        } catch (Exception $e) {
            error_log("Error tracking material access: " . $e->getMessage());
            return false;
        }
    }

    public function canAccessMaterial($userId, $materialId) {
        try {
            $query = "SELECT cm.required_lesson_id,
                             CASE 
                                WHEN cm.required_lesson_id IS NOT NULL THEN 
                                    (SELECT CASE WHEN cp.progress_percentage >= 100 THEN 1 ELSE 0 END 
                                     FROM {$this->progressTable} cp 
                                     WHERE cp.user_id = :user_id AND cp.post_id = cm.required_lesson_id)
                                ELSE 1 
                             END as can_access
                      FROM {$this->materialsTable} cm
                      WHERE cm.id = :material_id AND cm.is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':material_id', $materialId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? (bool)$result['can_access'] : false;
        } catch (Exception $e) {
            error_log("Error checking material access: " . $e->getMessage());
            return false;
        }
    }

    public function getRecommendedMaterials($userId, $courseId, $limit = 5) {
        try {
            $query = "SELECT cm.*, cm2.title as module_title,
                             uma.download_count as user_download_count
                      FROM {$this->materialsTable} cm
                      JOIN {$this->modulesTable} cm2 ON cm.module_id = cm2.id
                      LEFT JOIN {$this->accessTable} uma ON uma.material_id = cm.id AND uma.user_id = :user_id
                      WHERE cm2.course_id = :course_id AND cm.is_active = 1
                      AND (cm.required_lesson_id IS NULL OR 
                           cm.required_lesson_id IN (
                               SELECT cp.post_id FROM {$this->progressTable} cp 
                               WHERE cp.user_id = :user_id AND cp.progress_percentage >= 100
                           ))
                      AND uma.id IS NULL -- Not yet downloaded
                      ORDER BY cm.download_count DESC, cm.created_at DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recommended materials: " . $e->getMessage());
            return [];
        }
    }

    public function updateMaterial($id, $title, $description, $coverImage = null, $coverImagePath = null, $isActive = true, $requiredLessonId = null, $relatedLessonId = null, $orderIndex = 0) {
        try {
            $query = "UPDATE {$this->materialsTable} 
                      SET title = :title, description = :description, is_active = :is_active, 
                          required_lesson_id = :required_lesson_id, related_lesson_id = :related_lesson_id, 
                          order_index = :order_index";
            $params = [
                ':id' => $id, 
                ':title' => $title, 
                ':description' => $description, 
                ':is_active' => $isActive,
                ':required_lesson_id' => $requiredLessonId,
                ':related_lesson_id' => $relatedLessonId,
                ':order_index' => $orderIndex
            ];
            
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

    // Course Enrollment and Analytics Methods
    public function getCourseEnrollments($courseId) {
        try {
            $query = "SELECT 
                        ce.*,
                        u.username,
                        u.email,
                        COALESCE(MAX(cp.updated_at), ce.enrolled_at) as last_activity
                      FROM {$this->enrollmentsTable} ce
                      JOIN users u ON ce.user_id = u.id
                      LEFT JOIN {$this->progressTable} cp ON ce.user_id = cp.user_id AND ce.course_id = cp.course_id
                      WHERE ce.course_id = :course_id
                      GROUP BY ce.id, u.username, u.email
                      ORDER BY ce.enrolled_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting course enrollments: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentEnrollments($limit = 10) {
        try {
            $query = "SELECT 
                        ce.*,
                        u.username,
                        c.title as course_title
                      FROM {$this->enrollmentsTable} ce
                      JOIN users u ON ce.user_id = u.id
                      JOIN {$this->coursesTable} c ON ce.course_id = c.id
                      WHERE ce.is_active = 1
                      ORDER BY ce.enrolled_at DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent enrollments: " . $e->getMessage());
            return [];
        }
    }

    public function getCourseAnalytics($courseId) {
        try {
            // Get basic enrollment stats
            $enrollmentQuery = "SELECT 
                                  COUNT(*) as total_enrollments,
                                  COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) as total_completions,
                                  AVG(CASE WHEN completed_at IS NOT NULL THEN 
                                    TIMESTAMPDIFF(HOUR, enrolled_at, completed_at) 
                                  END) as avg_completion_time
                                FROM {$this->enrollmentsTable}
                                WHERE course_id = :course_id AND is_active = 1";
            
            $stmt = $this->conn->prepare($enrollmentQuery);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $enrollmentStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get module progress
            $moduleQuery = "SELECT 
                              cm.id,
                              cm.title,
                              COUNT(DISTINCT cp.user_id) as users_started,
                              COUNT(CASE WHEN cp.progress_percentage = 100 THEN 1 END) as users_completed,
                              AVG(cp.progress_percentage) as avg_progress
                            FROM {$this->modulesTable} cm
                            LEFT JOIN {$this->postsTable} p ON p.course_module_id = cm.id
                            LEFT JOIN {$this->progressTable} cp ON cp.post_id = p.id
                            WHERE cm.course_id = :course_id AND cm.is_active = 1
                            GROUP BY cm.id, cm.title
                            ORDER BY cm.order_index ASC";
            
            $stmt = $this->conn->prepare($moduleQuery);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $moduleProgress = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get recent activity
            $activityQuery = "SELECT 
                                u.username,
                                cp.updated_at,
                                CASE 
                                  WHEN cp.progress_percentage = 100 THEN 'completed lesson'
                                  WHEN cp.progress_percentage > 0 THEN 'started lesson'
                                  ELSE 'enrolled'
                                END as action
                              FROM {$this->progressTable} cp
                              JOIN users u ON cp.user_id = u.id
                              WHERE cp.course_id = :course_id
                              ORDER BY cp.updated_at DESC
                              LIMIT 10";
            
            $stmt = $this->conn->prepare($activityQuery);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total_enrollments' => $enrollmentStats['total_enrollments'] ?? 0,
                'total_completions' => $enrollmentStats['total_completions'] ?? 0,
                'completion_rate' => $enrollmentStats['total_enrollments'] > 0 ? 
                    round(($enrollmentStats['total_completions'] / $enrollmentStats['total_enrollments']) * 100, 1) : 0,
                'avg_time_spent' => round($enrollmentStats['avg_completion_time'] ?? 0, 1),
                'module_progress' => array_map(function($module) {
                    return [
                        'title' => $module['title'],
                        'completion_rate' => $module['users_started'] > 0 ? 
                            round(($module['users_completed'] / $module['users_started']) * 100, 1) : 0
                    ];
                }, $moduleProgress),
                'recent_activity' => array_map(function($activity) {
                    return [
                        'username' => $activity['username'],
                        'action' => $activity['action'],
                        'date' => date('M j, Y g:i A', strtotime($activity['updated_at']))
                    ];
                }, $recentActivity)
            ];
        } catch (Exception $e) {
            error_log("Error getting course analytics: " . $e->getMessage());
            return [
                'total_enrollments' => 0,
                'total_completions' => 0,
                'completion_rate' => 0,
                'avg_time_spent' => 0,
                'module_progress' => [],
                'recent_activity' => []
            ];
        }
    }

    // Progressive Learning Methods
    
    public function getModulesWithPostsByCourse($courseId, $activeOnly = true) {
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
            $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get posts for each module
            foreach ($modules as &$module) {
                $module['posts'] = $this->getPostsByModule($module['id'], $activeOnly);
            }
            
            return $modules;
        } catch (Exception $e) {
            error_log("Error getting modules with posts: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPostsByModule($moduleId, $activeOnly = true) {
        try {
            $whereClause = $activeOnly ? "AND p.status = 'published'" : "";
            $query = "SELECT p.*, c.name as category_name,
                             CEIL(LENGTH(p.content) / 250) as reading_time
                      FROM {$this->postsTable} p
                      LEFT JOIN categories c ON p.category_id = c.id
                      WHERE p.course_module_id = :module_id {$whereClause}
                      ORDER BY p.lesson_order ASC, p.published_at ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':module_id', $moduleId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting posts by module: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUserCourseProgress($userId, $courseId) {
        try {
            $query = "SELECT cp.*, p.slug as post_slug
                      FROM {$this->progressTable} cp
                      JOIN {$this->postsTable} p ON cp.post_id = p.id
                      WHERE cp.user_id = :user_id AND cp.course_id = :course_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert to associative array with post_id as key
            $progress = [];
            foreach ($results as $row) {
                $progress[$row['post_id']] = $row;
            }
            
            return $progress;
        } catch (Exception $e) {
            error_log("Error getting user course progress: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUserCourseEnrollment($userId, $courseId) {
        try {
            $query = "SELECT * FROM {$this->enrollmentsTable} 
                      WHERE user_id = :user_id AND course_id = :course_id AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user course enrollment: " . $e->getMessage());
            return false;
        }
    }
    
    public function enrollUserInCourse($userId, $courseId) {
        try {
            $query = "INSERT INTO {$this->enrollmentsTable} (user_id, course_id) 
                      VALUES (:user_id, :course_id)
                      ON DUPLICATE KEY UPDATE is_active = 1, enrolled_at = CURRENT_TIMESTAMP";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':course_id', $courseId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error enrolling user in course: " . $e->getMessage());
            return false;
        }
    }
    
    public function trackLessonProgress($userId, $courseId, $moduleId, $postId, $progressPercentage = 0, $timeSpent = 0) {
        try {
            $query = "INSERT INTO {$this->progressTable} 
                      (user_id, course_id, module_id, post_id, progress_percentage, time_spent_minutes) 
                      VALUES (:user_id, :course_id, :module_id, :post_id, :progress_percentage, :time_spent)
                      ON DUPLICATE KEY UPDATE 
                      progress_percentage = :progress_percentage,
                      time_spent_minutes = time_spent_minutes + :time_spent,
                      updated_at = CURRENT_TIMESTAMP";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->bindParam(':module_id', $moduleId);
            $stmt->bindParam(':post_id', $postId);
            $stmt->bindParam(':progress_percentage', $progressPercentage);
            $stmt->bindParam(':time_spent', $timeSpent);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error tracking lesson progress: " . $e->getMessage());
            return false;
        }
    }
    
    public function completeLesson($userId, $courseId, $moduleId, $postId) {
        try {
            $query = "UPDATE {$this->progressTable} 
                      SET progress_percentage = 100, completed_at = CURRENT_TIMESTAMP
                      WHERE user_id = :user_id AND course_id = :course_id 
                      AND module_id = :module_id AND post_id = :post_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->bindParam(':module_id', $moduleId);
            $stmt->bindParam(':post_id', $postId);
            $result = $stmt->execute();
            
            // Check if course is completed and update enrollment
            if ($result) {
                $this->checkAndUpdateCourseCompletion($userId, $courseId);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error completing lesson: " . $e->getMessage());
            return false;
        }
    }
    
    private function checkAndUpdateCourseCompletion($userId, $courseId) {
        try {
            // Get total lessons in course
            $totalQuery = "SELECT COUNT(*) as total_lessons
                          FROM {$this->postsTable} p
                          JOIN {$this->modulesTable} cm ON p.course_module_id = cm.id
                          WHERE cm.course_id = :course_id AND p.status = 'published'";
            $stmt = $this->conn->prepare($totalQuery);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $totalLessons = $stmt->fetch(PDO::FETCH_ASSOC)['total_lessons'];
            
            // Get completed lessons for user
            $completedQuery = "SELECT COUNT(*) as completed_lessons
                              FROM {$this->progressTable}
                              WHERE user_id = :user_id AND course_id = :course_id 
                              AND completed_at IS NOT NULL";
            $stmt = $this->conn->prepare($completedQuery);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $completedLessons = $stmt->fetch(PDO::FETCH_ASSOC)['completed_lessons'];
            
            // If all lessons completed, mark course as completed
            if ($totalLessons > 0 && $completedLessons >= $totalLessons) {
                $updateQuery = "UPDATE {$this->enrollmentsTable} 
                               SET completed_at = CURRENT_TIMESTAMP
                               WHERE user_id = :user_id AND course_id = :course_id";
                $stmt = $this->conn->prepare($updateQuery);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':course_id', $courseId);
                $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Error checking course completion: " . $e->getMessage());
        }
    }
    
    public function getCoursesWithProgress($userId = null) {
        try {
            $baseQuery = "SELECT c.*, 
                         COUNT(DISTINCT cm.id) as total_modules,
                         COUNT(DISTINCT p.id) as total_lessons";
            
            if ($userId) {
                $baseQuery .= ", COUNT(DISTINCT cp.id) as completed_lessons,
                              ce.enrolled_at, ce.completed_at as course_completed_at";
            }
            
            $baseQuery .= " FROM {$this->coursesTable} c
                           LEFT JOIN {$this->modulesTable} cm ON c.id = cm.course_id AND cm.is_active = 1
                           LEFT JOIN {$this->postsTable} p ON cm.id = p.course_module_id AND p.status = 'published'";
            
            if ($userId) {
                $baseQuery .= " LEFT JOIN {$this->enrollmentsTable} ce ON c.id = ce.course_id AND ce.user_id = :user_id AND ce.is_active = 1
                               LEFT JOIN {$this->progressTable} cp ON c.id = cp.course_id AND cp.user_id = :user_id AND cp.completed_at IS NOT NULL";
            }
            
            $baseQuery .= " WHERE c.is_active = 1
                           GROUP BY c.id
                           ORDER BY c.title ASC";
            
            $stmt = $this->conn->prepare($baseQuery);
            if ($userId) {
                $stmt->bindParam(':user_id', $userId);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting courses with progress: " . $e->getMessage());
            return [];
        }
    }
}
?>
