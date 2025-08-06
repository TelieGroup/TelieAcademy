<?php
require_once dirname(__DIR__) . '/config/database.php';

class Media {
    private $conn;
    private $table = 'media_files';

    public function __construct() {
        $this->conn = getDB();
    }

    // Upload media file
    public function uploadMedia($mediaData) {
        try {
            // Ensure all required fields are present
            $filename = $mediaData['filename'] ?? '';
            $originalName = $mediaData['original_name'] ?? '';
            $fileType = $mediaData['file_type'] ?? '';
            $fileSize = $mediaData['file_size'] ?? 0;
            $filePath = $mediaData['file_path'] ?? '';
            $uploadedBy = $mediaData['uploaded_by'] ?? 0;
            $description = $mediaData['description'] ?? '';
            $altText = $mediaData['alt_text'] ?? '';
            $tags = $mediaData['tags'] ?? '';
            
            $query = "INSERT INTO " . $this->table . " 
                     (filename, original_name, file_type, file_size, file_path, uploaded_by, description, alt_text, tags) 
                     VALUES (:filename, :original_name, :file_type, :file_size, :file_path, :uploaded_by, :description, :alt_text, :tags)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':filename', $filename);
            $stmt->bindValue(':original_name', $originalName);
            $stmt->bindValue(':file_type', $fileType);
            $stmt->bindValue(':file_size', $fileSize, PDO::PARAM_INT);
            $stmt->bindValue(':file_path', $filePath);
            $stmt->bindValue(':uploaded_by', $uploadedBy, PDO::PARAM_INT);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':alt_text', $altText);
            $stmt->bindValue(':tags', $tags);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Media uploaded successfully', 'id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to upload media'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error uploading media: ' . $e->getMessage()];
        }
    }

    // Get all media files
    public function getAllMedia($limit = null, $offset = 0) {
        try {
            $query = "SELECT m.*, u.username as uploaded_by_name 
                     FROM " . $this->table . " m 
                     LEFT JOIN users u ON m.uploaded_by = u.id 
                     ORDER BY m.uploaded_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get media by ID
    public function getMediaById($mediaId) {
        try {
            $query = "SELECT m.*, u.username as uploaded_by_name 
                     FROM " . $this->table . " m 
                     LEFT JOIN users u ON m.uploaded_by = u.id 
                     WHERE m.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $mediaId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Update media
    public function updateMedia($mediaId, $updateData) {
        try {
            // Ensure all required fields are present
            $description = $updateData['description'] ?? '';
            $altText = $updateData['alt_text'] ?? '';
            $tags = $updateData['tags'] ?? '';
            
            $query = "UPDATE " . $this->table . " 
                     SET description = :description, alt_text = :alt_text, tags = :tags, updated_at = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':alt_text', $altText);
            $stmt->bindValue(':tags', $tags);
            $stmt->bindValue(':id', $mediaId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Media updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update media'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating media: ' . $e->getMessage()];
        }
    }

    // Delete media
    public function deleteMedia($mediaId) {
        try {
            // Get media info first
            $media = $this->getMediaById($mediaId);
            if (!$media) {
                return ['success' => false, 'message' => 'Media not found'];
            }
            
            // Delete from database
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $mediaId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Delete physical file
                $filePath = dirname(__DIR__) . '/' . $media['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                return ['success' => true, 'message' => 'Media deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete media'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error deleting media: ' . $e->getMessage()];
        }
    }

    // Get total media count
    public function getTotalMediaCount() {
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

    // Get total media size
    public function getTotalMediaSize() {
        try {
            $query = "SELECT SUM(file_size) as total_size FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total_size'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    // Get media count by type
    public function getMediaCountByType($type) {
        try {
            if ($type === 'image') {
                $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE file_type LIKE 'image/%'";
            } elseif ($type === 'document') {
                $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE file_type NOT LIKE 'image/%'";
            } else {
                return 0;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    // Search media
    public function searchMedia($searchTerm) {
        try {
            $query = "SELECT m.*, u.username as uploaded_by_name 
                     FROM " . $this->table . " m 
                     LEFT JOIN users u ON m.uploaded_by = u.id 
                     WHERE m.original_name LIKE :search 
                     OR m.description LIKE :search 
                     OR m.alt_text LIKE :search 
                     OR m.tags LIKE :search 
                     ORDER BY m.uploaded_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $searchPattern = '%' . $searchTerm . '%';
            $stmt->bindParam(':search', $searchPattern);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Get media by type
    public function getMediaByType($type, $limit = null) {
        try {
            if ($type === 'image') {
                $query = "SELECT m.*, u.username as uploaded_by_name 
                         FROM " . $this->table . " m 
                         LEFT JOIN users u ON m.uploaded_by = u.id 
                         WHERE m.file_type LIKE 'image/%' 
                         ORDER BY m.uploaded_at DESC";
            } elseif ($type === 'document') {
                $query = "SELECT m.*, u.username as uploaded_by_name 
                         FROM " . $this->table . " m 
                         LEFT JOIN users u ON m.uploaded_by = u.id 
                         WHERE m.file_type NOT LIKE 'image/%' 
                         ORDER BY m.uploaded_at DESC";
            } else {
                return [];
            }
            
            if ($limit) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Format file size
    public function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    // Get media statistics
    public function getMediaStatistics() {
        try {
            $stats = [];
            
            // Total files
            $stats['total_files'] = $this->getTotalMediaCount();
            
            // Total size
            $stats['total_size'] = $this->getTotalMediaSize();
            $stats['total_size_formatted'] = $this->formatFileSize($stats['total_size']);
            
            // Images count
            $stats['images_count'] = $this->getMediaCountByType('image');
            
            // Documents count
            $stats['documents_count'] = $this->getMediaCountByType('document');
            
            // Recent upload
            $query = "SELECT original_name, uploaded_at FROM " . $this->table . " ORDER BY uploaded_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $recent = $stmt->fetch();
            $stats['recent_upload'] = $recent ? $recent['original_name'] . ' (' . date('M j, Y', strtotime($recent['uploaded_at'])) . ')' : 'No uploads';
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Validate file type
    public function isValidFileType($fileType) {
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'text/plain', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        return in_array($fileType, $allowedTypes);
    }

    // Validate file size
    public function isValidFileSize($fileSize, $maxSize = 5242880) { // 5MB default
        return $fileSize <= $maxSize;
    }
}
?> 