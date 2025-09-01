<?php
require_once '../config/session.php';
require_once '../includes/Course.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

$course = new Course();

// Get course ID from URL
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if (!$courseId) {
    header('Location: courses.php');
    exit;
}

// Get course details
$courseData = $course->getCourseById($courseId);
if (!$courseData) {
    header('Location: courses.php');
    exit;
}

// Handle form submissions and AJAX requests
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
}

if ($action) {
    switch ($action) {
        case 'create_module':
            $title = trim($_POST['title']);
            $slug = strtolower(str_replace(' ', '-', $title));
            $description = trim($_POST['description']);
            $orderIndex = (int)$_POST['order_index'];
            
            if (!empty($title) && !empty($description)) {
                $moduleId = $course->createModule($courseId, $title, $slug, $description, $orderIndex);
                
                if ($moduleId) {
                    $successMessage = "Module created successfully!";
                    
                    // Handle file uploads if any
                    if (isset($_FILES['course_files']) && !empty($_FILES['course_files']['name'][0])) {
                        $materialTitle = trim($_POST['material_title'] ?? '');
                        $materialDescription = trim($_POST['material_description'] ?? '');
                        
                        if (empty($materialTitle)) {
                            $materialTitle = $title . ' Materials';
                        }
                        if (empty($materialDescription)) {
                            $materialDescription = 'Course materials for ' . $title;
                        }
                        
                        $uploadedFiles = 0;
                        $totalFiles = count($_FILES['course_files']['name']);
                        
                        for ($i = 0; $i < $totalFiles; $i++) {
                            if ($_FILES['course_files']['error'][$i] === UPLOAD_ERR_OK) {
                                $fileName = $_FILES['course_files']['name'][$i];
                                $fileSize = $_FILES['course_files']['size'][$i];
                                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                
                                // Validate file type
                                $allowedTypes = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];
                                if (in_array($fileType, $allowedTypes)) {
                                    // Create uploads directory if it doesn't exist
                                    $uploadDir = '../uploads/course_materials/';
                                    if (!is_dir($uploadDir)) {
                                        mkdir($uploadDir, 0755, true);
                                    }
                                    
                                    // Generate unique filename
                                    $uniqueName = uniqid() . '_' . $fileName;
                                    $filePath = $uploadDir . $uniqueName;
                                    
                                    // Handle cover image if uploaded
                                    $coverImage = null;
                                    $coverImagePath = null;
                                    
                                    if (isset($_FILES['material_cover']) && $_FILES['material_cover']['error'] === UPLOAD_ERR_OK) {
                                        $coverFileName = $_FILES['material_cover']['name'];
                                        $coverFileType = strtolower(pathinfo($coverFileName, PATHINFO_EXTENSION));
                                        
                                        // Validate image type
                                        $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
                                        if (in_array($coverFileType, $allowedImageTypes)) {
                                            // Create uploads directory for images if it doesn't exist
                                            $imageUploadDir = '../uploads/material_covers/';
                                            if (!is_dir($imageUploadDir)) {
                                                mkdir($imageUploadDir, 0755, true);
                                            }
                                            
                                            // Generate unique filename for cover image
                                            $uniqueCoverName = uniqid() . '_cover_' . $coverFileName;
                                            $coverImagePath = $imageUploadDir . $uniqueCoverName;
                                            
                                            if (move_uploaded_file($_FILES['material_cover']['tmp_name'], $coverImagePath)) {
                                                $coverImage = $coverFileName;
                                            }
                                        }
                                    }
                                    
                                    if (move_uploaded_file($_FILES['course_files']['tmp_name'][$i], $filePath)) {
                                        // Create material record with cover image
                                        if ($course->createMaterial($moduleId, $materialTitle, $materialDescription, $fileName, $filePath, $fileSize, $fileType, $coverImage, $coverImagePath)) {
                                            $uploadedFiles++;
                                        }
                                    }
                                }
                            }
                        }
                        
                        if ($uploadedFiles > 0) {
                            $successMessage .= " and {$uploadedFiles} file(s) uploaded successfully!";
                        }
                    }
                } else {
                    $errorMessage = "Failed to create module.";
                }
            } else {
                $errorMessage = "Please fill in all required fields.";
            }
            break;
            
        case 'update_module':
            $id = $_POST['module_id'];
            $title = trim($_POST['title']);
            $slug = strtolower(str_replace(' ', '-', $title));
            $description = trim($_POST['description']);
            $orderIndex = (int)$_POST['order_index'];
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            if (!empty($title) && !empty($description)) {
                if ($course->updateModule($id, $title, $slug, $description, $orderIndex, $isActive)) {
                    $successMessage = "Module updated successfully!";
                } else {
                    $errorMessage = "Failed to update module.";
                }
            } else {
                $errorMessage = "Please fill in all required fields.";
            }
            break;
            
        case 'delete_module':
            $id = $_POST['module_id'];
            if ($course->deleteModule($id)) {
                $successMessage = "Module deleted successfully!";
            } else {
                $errorMessage = "Failed to delete module.";
            }
            break;
            
        case 'add_material':
            $moduleId = (int)$_POST['module_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $requiredLessonId = !empty($_POST['required_lesson_id']) ? (int)$_POST['required_lesson_id'] : null;
            $relatedLessonId = !empty($_POST['related_lesson_id']) ? (int)$_POST['related_lesson_id'] : null;
            $orderIndex = isset($_POST['order_index']) ? (int)$_POST['order_index'] : 0;
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            
            if (!empty($title) && !empty($description) && $moduleId > 0) {
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $fileName = $_FILES['file']['name'];
                    $fileSize = $_FILES['file']['size'];
                    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Validate file type
                    $allowedTypes = ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
                    
                    // Validate file size (max 50MB)
                    $maxSize = 50 * 1024 * 1024; // 50MB
                    if ($fileSize > $maxSize) {
                        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum allowed size is 50MB.']);
                            exit;
                        } else {
                            $errorMessage = "File size too large. Maximum allowed size is 50MB.";
                            break;
                        }
                    }
                    
                    if (in_array($fileType, $allowedTypes)) {
                        // Create uploads directory if it doesn't exist
                        $uploadDir = '../uploads/course_materials/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        // Generate unique filename
                        $uniqueName = uniqid() . '_' . $fileName;
                        $filePath = $uploadDir . $uniqueName;
                        
                        // Handle cover image if uploaded
                        $coverImage = null;
                        $coverImagePath = null;
                        
                        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                            $coverFileName = $_FILES['cover_image']['name'];
                            $coverFileType = strtolower(pathinfo($coverFileName, PATHINFO_EXTENSION));
                            
                            // Validate image type
                            $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
                            if (in_array($coverFileType, $allowedImageTypes)) {
                                // Create uploads directory for images if it doesn't exist
                                $imageUploadDir = '../uploads/material_covers/';
                                if (!is_dir($imageUploadDir)) {
                                    mkdir($imageUploadDir, 0755, true);
                                }
                                
                                // Generate unique filename for cover image
                                $uniqueCoverName = uniqid() . '_cover_' . $coverFileName;
                                $coverImagePath = $imageUploadDir . $uniqueCoverName;
                                
                                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverImagePath)) {
                                    $coverImage = $coverFileName;
                                }
                            }
                        }
                        
                        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                            if ($course->createMaterial($moduleId, $title, $description, $fileName, $filePath, $fileSize, $fileType, $coverImage, $coverImagePath, $requiredLessonId, $relatedLessonId, $orderIndex)) {
                                // Check if this is an AJAX request
                                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                                    header('Content-Type: application/json');
                                    echo json_encode(['success' => true, 'message' => 'Material uploaded successfully!']);
                                    exit;
                                } else {
                                    $successMessage = "Material uploaded successfully!";
                                }
                            } else {
                                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                                    header('Content-Type: application/json');
                                    echo json_encode(['success' => false, 'message' => 'Failed to create material record.']);
                                    exit;
                                } else {
                                    $errorMessage = "Failed to create material record.";
                                }
                            }
                        } else {
                            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                                header('Content-Type: application/json');
                                echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
                                exit;
                            } else {
                                $errorMessage = "Failed to upload file.";
                            }
                        }
                    } else {
                        $errorMessage = "Invalid file type. Only PDF, PPT, PPTX, DOC, DOCX files are allowed.";
                    }
                } else {
                    $errorMessage = "Please select a valid file.";
                }
            } else {
                $errorMessage = "Please fill in all required fields.";
            }
            break;
            
        case 'get_materials':
            // Handle AJAX request for materials
            if (isset($_GET['module_id'])) {
                $moduleId = (int)$_GET['module_id'];
                
                // Debug: Log the request
                error_log("get_materials called for module_id: $moduleId");
                
                $materials = $course->getMaterialsByModule($moduleId, true);
                
                // Debug: Log the result
                error_log("Materials found: " . count($materials));
                error_log("Materials data: " . print_r($materials, true));
                
                // Return JSON response
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'materials' => $materials,
                    'debug' => [
                        'module_id' => $moduleId,
                        'count' => count($materials),
                        'query' => "getMaterialsByModule($moduleId, true)"
                    ]
                ]);
                exit;
            }
            break;
            
        case 'delete_material':
            // Handle material deletion
            if (isset($_POST['material_id'])) {
                $materialId = (int)$_POST['material_id'];
                
                if ($course->deleteMaterial($materialId)) {
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Material deleted successfully!']);
                        exit;
                    } else {
                        $successMessage = "Material deleted successfully!";
                    }
                } else {
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Failed to delete material.']);
                        exit;
                    } else {
                        $errorMessage = "Failed to delete material.";
                    }
                }
            }
            break;
            
        case 'update_material':
            // Handle material update
            
            if (isset($_POST['material_id']) && isset($_POST['module_id'])) {
                $materialId = (int)$_POST['material_id'];
                $moduleId = (int)$_POST['module_id'];
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $requiredLessonId = !empty($_POST['required_lesson_id']) ? (int)$_POST['required_lesson_id'] : null;
                $relatedLessonId = !empty($_POST['related_lesson_id']) ? (int)$_POST['related_lesson_id'] : null;
                $orderIndex = isset($_POST['order_index']) ? (int)$_POST['order_index'] : 0;
                $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
                
                if (!empty($title) && !empty($description) && $materialId > 0) {
                    // Get current material to check if cover image needs updating
                    $currentMaterial = $course->getMaterialByIdForAdmin($materialId);
                    
                    if ($currentMaterial) {
                        $coverImage = $currentMaterial['cover_image'];
                        $coverImagePath = $currentMaterial['cover_image_path'];
                        
                        // Handle new cover image if uploaded
                        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                            $coverFileName = $_FILES['cover_image']['name'];
                            $coverFileType = strtolower(pathinfo($coverFileName, PATHINFO_EXTENSION));
                            
                            // Validate image type
                            $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
                            if (in_array($coverFileType, $allowedImageTypes)) {
                                // Create uploads directory for images if it doesn't exist
                                $imageUploadDir = '../uploads/material_covers/';
                                if (!is_dir($imageUploadDir)) {
                                    mkdir($imageUploadDir, 0755, true);
                                }
                                
                                // Generate unique filename for cover image
                                $uniqueCoverName = uniqid() . '_cover_' . $coverFileName;
                                $newCoverImagePath = $imageUploadDir . $uniqueCoverName;
                                
                                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $newCoverImagePath)) {
                                    // Delete old cover image if it exists
                                    if ($coverImagePath && file_exists($coverImagePath)) {
                                        unlink($coverImagePath);
                                    }
                                    
                                    $coverImage = $coverFileName;
                                    $coverImagePath = $newCoverImagePath;
                                }
                            }
                        }
                        
                        // Update the material
                        if ($course->updateMaterial($materialId, $title, $description, $coverImage, $coverImagePath, $isActive, $requiredLessonId, $relatedLessonId, $orderIndex)) {
                            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                                header('Content-Type: application/json');
                                echo json_encode(['success' => true, 'message' => 'Material updated successfully!']);
                                exit;
                            } else {
                                $successMessage = "Material updated successfully!";
                            }
                        } else {
                            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                                header('Content-Type: application/json');
                                echo json_encode(['success' => false, 'message' => 'Failed to update material.']);
                                exit;
                            } else {
                                $errorMessage = "Failed to update material.";
                            }
                        }
                    } else {
                        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => 'Material not found.']);
                            exit;
                        } else {
                            $errorMessage = "Material not found.";
                        }
                    }
                } else {
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
                        exit;
                    } else {
                        $errorMessage = "Please fill in all required fields.";
                    }
                }
            }
            break;
    }
}

// Get modules for this course
$modules = $course->getModulesByCourse($courseId, false);

include '../includes/head.php';
?>

<style>
/* Custom styles for course modules */
.file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.file-upload-area:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.file-upload-area.dragover {
    border-color: #28a745;
    background-color: #d4edda;
}

.material-count {
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
}

.modal-xl {
    max-width: 1140px;
}

/* Material card styles */
.material-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.material-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.material-card .card-body {
    padding: 0.75rem;
}

.material-card .btn-group {
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.material-card:hover .btn-group {
    opacity: 1;
}

/* Responsive material card layout */
@media (max-width: 768px) {
    .material-card .card-body {
        padding: 1rem;
    }
    
    .material-card .d-flex {
        flex-direction: column;
    }
    
    .material-card .me-3 {
        margin-right: 0 !important;
        margin-bottom: 1rem;
        text-align: center;
    }
    
    .material-card .cover-image-container {
        height: 100px;
        width: 150px;
        margin: 0 auto;
    }
    
    .material-card .cover-image {
        max-height: 100px;
        max-width: 150px;
    }
    
    .material-card .cover-fallback {
        height: 100px;
        width: 150px;
    }
    
    .material-card .btn-group {
        width: 100%;
        margin-top: 1rem;
        justify-content: center;
    }
    
    .material-card .btn-group .btn {
        flex: 1;
        min-width: 0;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .material-card .row .col-6 {
        margin-bottom: 0.5rem;
    }
    
    .material-card .flex-grow-1 {
        text-align: center;
    }
    
    .material-card .d-flex.justify-content-between {
        flex-direction: column;
        align-items: center;
    }
    
    .material-card .ms-2 {
        margin-left: 0 !important;
        margin-top: 1rem;
    }
}

@media (max-width: 576px) {
    .material-card .btn-group {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .material-card .btn-group .btn {
        width: 100%;
        margin: 0;
    }
    
    .material-card .row .col-6 {
        width: 100%;
        text-align: center;
    }
    
    .material-card .cover-image-container {
        height: 120px;
        width: 180px;
    }
    
    .material-card .cover-image {
        max-height: 120px;
        max-width: 180px;
    }
    
    .material-card .cover-fallback {
        height: 120px;
        width: 180px;
    }
}

/* Additional responsive improvements */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        width: 100%;
    }
    
    .d-flex.gap-2 .btn {
        width: 100%;
    }
    
    .breadcrumb {
        font-size: 0.875rem;
    }
    
    .h3 {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
}

/* Toast notifications */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

/* Loading states */
.btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Cover image styles */
.cover-image-container {
    position: relative;
    height: 80px;
    width: 120px;
}

.cover-image {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 2;
    max-height: 80px;
    max-width: 120px;
    object-fit: cover;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.cover-fallback {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
    height: 80px;
    width: 120px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($courseData['title']); ?> - Modules</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-book text-primary"></i>
                        <?php echo htmlspecialchars($courseData['title']); ?> - Modules
                    </h1>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($courseData['description']); ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="courses.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Courses
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModuleModal">
                        <i class="fas fa-plus me-2"></i>Add New Module
                    </button>

                </div>
            </div>

            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            


            <!-- Modules List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Course Modules
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($modules)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-book fa-3x mb-3"></i>
                            <p>No modules found for this course. Create your first module to get started!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Order</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $module): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $module['order_index']; ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($module['title']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($module['slug']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars(substr($module['description'], 0, 100)) . (strlen($module['description']) > 100 ? '...' : ''); ?>
                                                <?php
                                                // Get materials count for this module
                                                $materialsCount = $course->getMaterialsByModule($module['id'], true);
                                                $count = count($materialsCount);
                                                if ($count > 0):
                                                ?>
                                                    <br><small class="text-info">
                                                        <i class="fas fa-file-alt me-1"></i><?php echo $count; ?> material(s)
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $module['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $module['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($module['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" 
                                                            onclick="manageMaterials(<?php echo $module['id']; ?>, '<?php echo htmlspecialchars($module['title']); ?>')" 
                                                            title="Manage Materials">
                                                        <i class="fas fa-file-alt"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" 
                                                            onclick="editModule(<?php echo $module['id']; ?>, '<?php echo htmlspecialchars($module['title']); ?>', '<?php echo htmlspecialchars($module['description']); ?>', <?php echo $module['order_index']; ?>, <?php echo $module['is_active'] ? 'true' : 'false'; ?>)" 
                                                            title="Edit Module">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" 
                                                            onclick="deleteModule(<?php echo $module['id']; ?>, '<?php echo htmlspecialchars($module['title']); ?>')" 
                                                            title="Delete Module">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Module Modal -->
<div class="modal fade" id="createModuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="createModuleForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_module">
                    

                    
                    <!-- Module Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Module Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="order_index" class="form-label">Order Index</label>
                                <input type="number" class="form-control" id="order_index" name="order_index" value="0" min="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <!-- File Upload Section -->
                    <hr>
                    <h6 class="text-primary">
                        <i class="fas fa-file-upload me-2"></i>Upload Course Materials
                    </h6>
                    <p class="text-muted small">You can upload multiple files for this module. Supported formats: PDF, PPT, PPTX, DOC, DOCX</p>
                    
                    <div class="mb-3">
                        <label for="material_title" class="form-label">Material Title</label>
                        <input type="text" class="form-control" id="material_title" name="material_title" placeholder="e.g., Chapter 1 Notes, Lecture Slides">
                    </div>
                    
                    <div class="mb-3">
                        <label for="material_description" class="form-label">Material Description</label>
                        <textarea class="form-control" id="material_description" name="material_description" rows="2" placeholder="Brief description of the material"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="course_files" class="form-label">Select Files</label>
                        <input type="file" class="form-control" id="course_files" name="course_files[]" multiple accept=".pdf,.ppt,.pptx,.doc,.docx">
                        <div class="form-text">You can select multiple files. Maximum size per file: 10MB</div>
                    </div>
                    
                    <div id="filePreview" class="mb-3" style="display: none;">
                        <label class="form-label">Selected Files:</label>
                        <div id="fileList" class="border rounded p-2 bg-light"></div>
                    </div>
                    
                    <!-- Cover Image Section -->
                    <div class="mb-3">
                        <label for="material_cover" class="form-label">Cover Image (Optional)</label>
                        <input type="file" class="form-control" id="material_cover" name="material_cover" accept="image/*">
                        <div class="form-text">Upload a cover image for the material. Supported formats: JPG, PNG, GIF. Max size: 2MB</div>
                    </div>
                    
                    <div id="coverPreview" class="mb-3" style="display: none;">
                        <label class="form-label">Cover Preview:</label>
                        <div class="border rounded p-2 bg-light text-center">
                            <img id="coverImagePreview" src="" alt="Cover Preview" class="img-fluid" style="max-height: 150px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button type="button" class="btn btn-primary" id="submitModuleBtn" onclick="submitModuleForm()">
                        <i class="fas fa-plus me-2"></i>Create Module & Upload Files
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_module">
                    <input type="hidden" name="module_id" id="edit_module_id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Module Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_order_index" class="form-label">Order Index</label>
                        <input type="number" class="form-control" id="edit_order_index" name="order_index" min="0">
                        <div class="form-text">Lower numbers appear first</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" checked>
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone and will also delete all associated materials.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Manage Materials Modal -->
<div class="modal fade" id="manageMaterialsModal" tabindex="-1" aria-labelledby="manageMaterialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageMaterialsModalLabel">Manage Module Materials</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Existing Materials -->
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-list me-2"></i>Current Materials
                        </h6>
                        <div id="materialsList" class="mb-4">
                            <!-- Materials will be loaded here -->
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Add New Material -->
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-plus me-2"></i>Add New Material
                        </h6>
                        <form id="addMaterialForm" enctype="multipart/form-data">
                            <input type="hidden" id="material_module_id" name="module_id">
                            
                            <!-- Basic Information -->
                            <div class="mb-3">
                                <label for="new_material_title" class="form-label">Material Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="new_material_title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_material_description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="new_material_description" name="description" rows="2" required></textarea>
                            </div>
                            
                            <!-- Progressive Learning Fields -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="new_material_order" class="form-label">Display Order</label>
                                        <input type="number" class="form-control" id="new_material_order" name="order_index" value="0" min="0" max="999">
                                        <div class="form-text">Lower = higher priority</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="new_material_active" class="form-label">Status</label>
                                        <select class="form-select" id="new_material_active" name="is_active">
                                            <option value="1" selected>Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_material_required_lesson" class="form-label">Required Lesson (Optional)</label>
                                <select class="form-select" id="new_material_required_lesson" name="required_lesson_id">
                                    <option value="">No requirement - Always accessible</option>
                                    <!-- Will be populated by JavaScript based on selected module -->
                                </select>
                                <div class="form-text">Users must complete this lesson before accessing the material</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_material_related_lesson" class="form-label">Related Lesson (Optional)</label>
                                <select class="form-select" id="new_material_related_lesson" name="related_lesson_id">
                                    <option value="">Not related to a specific lesson</option>
                                    <!-- Will be populated by JavaScript based on selected module -->
                                </select>
                                <div class="form-text">Material will appear in the lesson page</div>
                            </div>
                            
                            <!-- File Upload -->
                            <div class="mb-3">
                                <label for="new_material_file" class="form-label">Select File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="new_material_file" name="file" accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.zip,.rar" required>
                                <div class="form-text"><strong>Allowed:</strong> PDF, Office, Archives | <strong>Max:</strong> 50MB</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_material_cover" class="form-label">Cover Image (Optional)</label>
                                <input type="file" class="form-control" id="new_material_cover" name="cover_image" accept="image/*">
                                <div class="form-text">JPG, PNG, GIF (Max: 2MB)</div>
                            </div>
                            
                            <div id="newCoverPreview" class="mb-3" style="display: none;">
                                <label class="form-label">Cover Preview:</label>
                                <div class="border rounded p-2 bg-light text-center">
                                    <img id="newCoverImagePreview" src="" alt="Cover Preview" class="img-fluid" style="max-height: 100px;">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload me-1"></i>Upload Material
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Material Modal -->
<div class="modal fade" id="editMaterialModal" tabindex="-1" aria-labelledby="editMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMaterialModalLabel">Edit Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMaterialForm" action="javascript:void(0);" method="post" enctype="multipart/form-data" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="edit_material_id" name="material_id">
                    <input type="hidden" id="edit_material_module_id" name="module_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_material_title" class="form-label">Material Title</label>
                                <input type="text" class="form-control" id="edit_material_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_material_file_type" class="form-label">File Type</label>
                                <input type="text" class="form-control" id="edit_material_file_type" readonly>
                                <div class="form-text">File type cannot be changed</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_material_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_material_description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <!-- Progressive Learning Fields -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_material_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="edit_material_order" name="order_index" value="0" min="0" max="999">
                                <div class="form-text">Lower = higher priority</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_material_active" class="form-label">Status</label>
                                <select class="form-select" id="edit_material_active" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_material_downloads" class="form-label">Downloads</label>
                                <input type="text" class="form-control" id="edit_material_downloads" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_material_required_lesson" class="form-label">Required Lesson (Optional)</label>
                                <select class="form-select" id="edit_material_required_lesson" name="required_lesson_id">
                                    <option value="">No requirement - Always accessible</option>
                                    <!-- Will be populated by JavaScript -->
                                </select>
                                <div class="form-text">Users must complete this lesson before accessing</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_material_related_lesson" class="form-label">Related Lesson (Optional)</label>
                                <select class="form-select" id="edit_material_related_lesson" name="related_lesson_id">
                                    <option value="">Not related to a specific lesson</option>
                                    <!-- Will be populated by JavaScript -->
                                </select>
                                <div class="form-text">Material will appear in the lesson page</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_material_file_name" class="form-label">Current File</label>
                                <input type="text" class="form-control" id="edit_material_file_name" readonly>
                                <div class="form-text">Current file cannot be changed</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_material_file_size" class="form-label">File Size</label>
                                <input type="text" class="form-control" id="edit_material_file_size" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_material_cover" class="form-label">Update Cover Image (Optional)</label>
                        <input type="file" class="form-control" id="edit_material_cover" name="cover_image" accept="image/*">
                        <div class="form-text">Leave empty to keep current cover image. JPG, PNG, GIF (Max: 2MB)</div>
                    </div>
                    
                    <div id="editCoverPreview" class="mb-3" style="display: none;">
                        <label class="form-label">New Cover Preview:</label>
                        <div class="border rounded p-2 bg-light text-center">
                            <img id="editCoverImagePreview" src="" alt="Cover Preview" class="img-fluid" style="max-height: 150px;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Cover Image:</label>
                        <div id="currentCoverDisplay" class="border rounded p-2 bg-light text-center">
                            <!-- Current cover will be displayed here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editModule(id, title, description, orderIndex, isActive) {
    document.getElementById('edit_module_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_order_index').value = orderIndex;
    document.getElementById('edit_is_active').checked = isActive;
    
    new bootstrap.Modal(document.getElementById('editModuleModal')).show();
}

function deleteModule(id, title) {
    document.getElementById('deleteItemName').textContent = title;
    document.getElementById('deleteForm').innerHTML = `
        <input type="hidden" name="action" value="delete_module">
        <input type="hidden" name="module_id" value="${id}">
        <button type="submit" class="btn btn-danger">Delete</button>
    `;
    
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

function manageMaterials(moduleId, moduleTitle) {
    console.log('Managing materials for module:', moduleId, moduleTitle);
    
    // Set module ID in the form
    document.getElementById('material_module_id').value = moduleId;
    
    // Update modal title
    document.getElementById('manageMaterialsModalLabel').textContent = `Manage Materials - ${moduleTitle}`;
    
    // Populate lesson dropdowns for the add new material form
    populateLessonDropdowns(moduleId, ['new_material_required_lesson', 'new_material_related_lesson']);
    
    // Load existing materials
    loadModuleMaterials(moduleId);
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('manageMaterialsModal'));
    modal.show();
}

function loadModuleMaterials(moduleId) {
    console.log('Loading materials for module:', moduleId);
    console.log('Current course ID:', getCurrentCourseId());
    
    // Show loading state
    document.getElementById('materialsList').innerHTML = `
        <div class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
            <p>Loading materials...</p>
        </div>
    `;
    
    const url = `?course_id=${getCurrentCourseId()}&action=get_materials&module_id=${moduleId}`;
    console.log('Fetching from URL:', url);
    
    // Fetch materials via AJAX
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        console.log('Raw response data:', data);
        try {
            // Try to parse as JSON first
            const jsonData = JSON.parse(data);
            console.log('Parsed JSON data:', jsonData);
            
            if (jsonData.success) {
                console.log('Materials to display:', jsonData.materials);
                displayMaterials(jsonData.materials);
            } else {
                console.error('Server returned error:', jsonData.message);
                displayMaterials([]);
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            // If not JSON, check if it's an error page
            if (data.includes('error') || data.includes('Error')) {
                console.error('Server error:', data);
                displayMaterials([]);
            } else {
                // Try to extract materials from HTML response
                console.log('Non-JSON response received, treating as empty materials');
                displayMaterials([]);
            }
        }
    })
    .catch(error => {
        console.error('Error loading materials:', error);
        document.getElementById('materialsList').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Failed to load materials: ${error.message}
                <button class="btn btn-sm btn-outline-danger ms-2" onclick="loadModuleMaterials(${moduleId})">
                    <i class="fas fa-redo me-1"></i>Retry
                </button>
            </div>
        `;
    });
}

function getCurrentCourseId() {
    // Extract course ID from current URL
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('course_id');
}

function displayMaterials(materials) {
    const materialsList = document.getElementById('materialsList');
    
    if (!materials || materials.length === 0) {
        materialsList.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-file-alt fa-2x mb-2"></i>
                <p>No materials found for this module.</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    materials.forEach(material => {
        // Fix cover image path - remove ../ if present and ensure correct admin path
        let coverImagePath = material.cover_image_path;
        if (coverImagePath && coverImagePath.startsWith('../')) {
            coverImagePath = coverImagePath.substring(3);
        }
        // Since we're in admin directory, we need to go up one level to access uploads
        if (coverImagePath && !coverImagePath.startsWith('../')) {
            coverImagePath = '../' + coverImagePath;
        }
        
        // Debug cover image path
        console.log('Material:', material.title);
        console.log('Original cover path:', material.cover_image_path);
        console.log('Fixed cover path:', coverImagePath);
        
        const coverImage = material.cover_image_path ? 
            `<div class="cover-image-container">
                <img src="${coverImagePath}" alt="Cover" class="cover-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" onload="this.style.display='block'; this.nextElementSibling.style.display='none'; console.log('Image loaded successfully:', this.src);">
                <div class="cover-fallback">
                    <i class="fas fa-file-${material.file_type === 'pdf' ? 'pdf' : 'word'} fa-2x text-muted"></i>
                </div>
            </div>` : 
            `<div class="cover-fallback">
                <i class="fas fa-file-${material.file_type === 'pdf' ? 'pdf' : 'word'} fa-2x text-muted"></i>
             </div>`;
        
        html += `
            <div class="card mb-2 material-card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="me-3">
                            ${coverImage}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${material.title}</h6>
                                    <p class="text-muted small mb-1">${material.description}</p>
                                    <div class="row text-muted small">
                                        <div class="col-6 col-sm-6">
                                            <i class="fas fa-file-${material.file_type === 'pdf' ? 'pdf' : 'word'} me-1"></i>
                                            <span class="d-none d-sm-inline">${material.file_name}</span>
                                            <span class="d-inline d-sm-none">${material.file_name.length > 15 ? material.file_name.substring(0, 15) + '...' : material.file_name}</span>
                                        </div>
                                        <div class="col-6 col-sm-6">
                                            <i class="fas fa-weight-hanging me-1"></i>
                                            ${(material.file_size / 1024).toFixed(1)} KB
                                        </div>
                                    </div>
                                    <div class="row text-muted small mt-1">
                                        <div class="col-6 col-sm-6">
                                            <i class="fas fa-download me-1"></i>
                                            ${material.download_count || 0} downloads
                                        </div>
                                        <div class="col-6 col-sm-6">
                                            <i class="fas fa-sort-numeric-down me-1"></i>
                                            Order: ${material.order_index || 0}
                                        </div>
                                    </div>
                                    <div class="row text-muted small mt-1">
                                        <div class="col-12">
                                            <i class="fas fa-calendar me-1"></i>
                                            <span class="d-none d-sm-inline">${new Date(material.created_at).toLocaleDateString()}</span>
                                            <span class="d-inline d-sm-none">${new Date(material.created_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})}</span>
                                        </div>
                                    </div>
                                    ${material.required_lesson_id || material.related_lesson_id ? `
                                    <div class="mt-2">
                                        ${material.required_lesson_id ? '<span class="badge bg-warning text-dark me-1"><i class="fas fa-lock me-1"></i>Requires Lesson</span>' : ''}
                                        ${material.related_lesson_id ? '<span class="badge bg-info me-1"><i class="fas fa-link me-1"></i>Linked to Lesson</span>' : ''}
                                        ${material.is_active == 0 ? '<span class="badge bg-secondary"><i class="fas fa-eye-slash me-1"></i>Inactive</span>' : '<span class="badge bg-success"><i class="fas fa-eye me-1"></i>Active</span>'}
                                    </div>
                                    ` : `
                                    <div class="mt-2">
                                        ${material.is_active == 0 ? '<span class="badge bg-secondary"><i class="fas fa-eye-slash me-1"></i>Inactive</span>' : '<span class="badge bg-success"><i class="fas fa-eye me-1"></i>Active</span>'}
                                    </div>
                                    `}
                                </div>
                                <div class="btn-group btn-group-sm ms-2">
                                    <button class="btn btn-outline-info btn-sm" data-material-id="${material.id}" data-material-title="${material.title}" data-material-description="${material.description}" data-order-index="${material.order_index || 0}" data-is-active="${material.is_active || 1}" data-downloads="${material.download_count || 0}" data-required-lesson="${material.required_lesson_id || ''}" data-related-lesson="${material.related_lesson_id || ''}" onclick="editMaterialByData(this)" title="Edit Material">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="downloadMaterial(${material.id})" title="Download Material">
                                        <i class="fas fa-download me-1"></i><span class="d-none d-sm-inline">Download</span>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteMaterial(${material.id})" title="Delete Material">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    materialsList.innerHTML = html;
    
    // After setting HTML, ensure proper image display
    setTimeout(() => {
        const coverImages = materialsList.querySelectorAll('.cover-image');
        coverImages.forEach(img => {
            if (img.complete) {
                // Image already loaded
                if (img.naturalWidth > 0) {
                    img.style.display = 'block';
                    const fallback = img.nextElementSibling;
                    if (fallback && fallback.classList.contains('cover-fallback')) {
                        fallback.style.display = 'none';
                    }
                } else {
                    // Image failed to load
                    img.style.display = 'none';
                    const fallback = img.nextElementSibling;
                    if (fallback && fallback.classList.contains('cover-fallback')) {
                        fallback.style.display = 'flex';
                    }
                }
            }
        });
    }, 100);
}

// Function to populate lesson dropdowns based on module
function populateLessonDropdowns(moduleId, targetSelectIds) {
    if (!moduleId) return;
    
    // Make AJAX request to get posts for this module
    fetch(`../api/get-module-posts.php?module_id=${moduleId}`)
        .then(response => response.json())
        .then(posts => {
            targetSelectIds.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    // Clear existing options except the first one
                    const firstOption = select.firstElementChild;
                    select.innerHTML = '';
                    select.appendChild(firstOption);
                    
                    // Add posts as options
                    posts.forEach(post => {
                        const option = document.createElement('option');
                        option.value = post.id;
                        option.textContent = `${post.title} (Lesson ${post.lesson_order})`;
                        select.appendChild(option);
                    });
                }
            });
        })
        .catch(error => {
            console.error('Error loading posts:', error);
        });
}

function editMaterialByData(button) {
    console.log('=== EDIT MATERIAL BY DATA CALLED ===');
    
    // Extract data from button attributes
    const materialId = parseInt(button.dataset.materialId);
    const title = button.dataset.materialTitle;
    const description = button.dataset.materialDescription;
    const orderIndex = parseInt(button.dataset.orderIndex) || 0;
    const isActive = parseInt(button.dataset.isActive) || 1;
    const downloads = parseInt(button.dataset.downloads) || 0;
    const requiredLessonId = button.dataset.requiredLesson ? parseInt(button.dataset.requiredLesson) : null;
    const relatedLessonId = button.dataset.relatedLesson ? parseInt(button.dataset.relatedLesson) : null;
    
    console.log('Data extracted from button:');
    console.log('- materialId:', materialId);
    console.log('- title:', title);
    console.log('- description:', description);
    console.log('- orderIndex:', orderIndex);
    console.log('- isActive:', isActive);
    console.log('- downloads:', downloads);
    console.log('- requiredLessonId:', requiredLessonId);
    console.log('- relatedLessonId:', relatedLessonId);
    
    // Call the original editMaterial function
    editMaterial(materialId, title, description, orderIndex, isActive, downloads, requiredLessonId, relatedLessonId);
}

function editMaterial(materialId, title, description, orderIndex = 0, isActive = 1, downloads = 0, requiredLessonId = null, relatedLessonId = null) {
    console.log('=== EDIT MATERIAL FUNCTION CALLED ===');
    console.log('Parameters received:');
    console.log('- materialId:', materialId);
    console.log('- title:', title);
    console.log('- description:', description);
    console.log('- orderIndex:', orderIndex);
    console.log('- isActive:', isActive);
    console.log('- downloads:', downloads);
    console.log('- requiredLessonId:', requiredLessonId);
    console.log('- relatedLessonId:', relatedLessonId);
    
    // Get the current module ID
    const moduleId = document.getElementById('material_module_id').value;
    
    // Set form values
    document.getElementById('edit_material_id').value = materialId;
    document.getElementById('edit_material_module_id').value = moduleId;
    document.getElementById('edit_material_title').value = title;
    document.getElementById('edit_material_description').value = description;
    
    // Set progressive learning fields
    document.getElementById('edit_material_order').value = orderIndex;
    document.getElementById('edit_material_active').value = isActive;
    document.getElementById('edit_material_downloads').value = downloads + ' downloads';
    
    // Populate lesson dropdowns and set selected values
    populateLessonDropdowns(moduleId, ['edit_material_required_lesson', 'edit_material_related_lesson']);
    
    // Set selected lesson values after populating dropdowns
    setTimeout(() => {
        if (requiredLessonId) {
            document.getElementById('edit_material_required_lesson').value = requiredLessonId;
        }
        if (relatedLessonId) {
            document.getElementById('edit_material_related_lesson').value = relatedLessonId;
        }
    }, 500);
    
    // Get material details for display
    const materialCard = event.target.closest('.material-card');
    const fileType = materialCard.querySelector('.fa-file-pdf, .fa-file-word, .fa-file-powerpoint, .fa-file-excel, .fa-file-archive, .fa-file-alt') 
        ? Array.from(materialCard.querySelectorAll('.fas')).find(icon => icon.classList.contains('fa-file-'))?.classList[1]?.replace('fa-file-', '').toUpperCase() || 'FILE'
        : 'FILE';
    const fileName = materialCard.querySelector('.text-muted.small')?.textContent.trim() || 'Unknown';
    const fileSize = materialCard.querySelector('.fa-hdd')?.nextSibling?.textContent.trim() || 'Unknown';
    
    // Set readonly fields
    document.getElementById('edit_material_file_type').value = fileType;
    document.getElementById('edit_material_file_name').value = fileName;
    document.getElementById('edit_material_file_size').value = fileSize;
    
    // Display current cover image
    const currentCover = materialCard.querySelector('.cover-image');
    const currentCoverDisplay = document.getElementById('currentCoverDisplay');
    
    if (currentCover && currentCover.src) {
        currentCoverDisplay.innerHTML = `
            <img src="${currentCover.src}" alt="Current Cover" class="img-fluid" style="max-height: 150px; border-radius: 4px;">
        `;
    } else {
        currentCoverDisplay.innerHTML = `
            <div class="text-muted py-3">
                <i class="fas fa-image fa-2x mb-2"></i>
                <p>No cover image</p>
            </div>
        `;
    }
    
    // Reset cover preview
    document.getElementById('editCoverPreview').style.display = 'none';
    document.getElementById('edit_material_cover').value = '';
    
    // Show the modal
    console.log('About to show edit material modal...');
    const modal = new bootstrap.Modal(document.getElementById('editMaterialModal'));
    console.log('Modal instance created:', modal);
    modal.show();
    console.log('Modal show() called');
}

function deleteMaterial(materialId) {
    if (confirm('Are you sure you want to delete this material? This action cannot be undone.')) {
        // Show loading state
        const deleteBtn = event.target.closest('button');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteBtn.disabled = true;
        
        // Submit delete request
        const formData = new FormData();
        formData.append('action', 'delete_material');
        formData.append('material_id', materialId);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            try {
                const jsonData = JSON.parse(data);
                if (jsonData.success) {
                    showToast('Material deleted successfully!', 'success');
                    // Reload materials list
                    const moduleId = document.getElementById('material_module_id').value;
                    loadModuleMaterials(moduleId);
                } else {
                    showToast(jsonData.message || 'Failed to delete material', 'error');
                }
            } catch (e) {
                showToast('Material deleted successfully!', 'success');
                // Reload materials list
                const moduleId = document.getElementById('material_module_id').value;
                loadModuleMaterials(moduleId);
            }
        })
        .catch(error => {
            console.error('Error deleting material:', error);
            showToast('Failed to delete material. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        });
    }
    
    // Edit material form submission handler
    console.log('Setting up edit material form submission handler');
    const editMaterialForm = document.getElementById('editMaterialForm');
    console.log('Edit material form element:', editMaterialForm);
    
    if (editMaterialForm) {
        console.log('Edit material form found, adding submit listener');
        
        editMaterialForm.addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            console.log('Event type:', e.type);
            console.log('Default prevented:', e.defaultPrevented);
            
            e.preventDefault();
            e.stopPropagation();
            
            console.log('After preventDefault - Default prevented:', e.defaultPrevented);
            
            console.log('Edit material form submitted');
            console.log('Form action:', this.action);
            console.log('Form method:', this.method);
            
            const formData = new FormData(this);
            formData.append('action', 'update_material');
            
            // Debug: Log form data
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
            submitBtn.disabled = true;
            
            // Submit form via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const jsonData = JSON.parse(data);
                    if (jsonData.success) {
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('editMaterialModal')).hide();
                        
                        // Show success message
                        showToast('Material updated successfully!', 'success');
                        
                        // Reload materials list
                        const moduleId = document.getElementById('material_module_id').value;
                        loadModuleMaterials(moduleId);
                        
                        // Reset form
                        this.reset();
                    } else {
                        showToast(jsonData.message || 'Failed to update material', 'error');
                    }
                } catch (e) {
                    // If not JSON, show generic message
                    showToast('Material updated successfully!', 'success');
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('editMaterialModal')).hide();
                    
                    // Reload materials list
                    const moduleId = document.getElementById('material_module_id').value;
                    loadModuleMaterials(moduleId);
                    
                    // Reset form
                    this.reset();
                }
            })
            .catch(error => {
                console.error('Error updating material:', error);
                showToast('Failed to update material. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}

function downloadMaterial(materialId) {
    // Show loading state
    const downloadBtn = event.target.closest('button');
    const originalText = downloadBtn.innerHTML;
    downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Downloading...';
    downloadBtn.disabled = true;
    
    // Open download in new tab
            const downloadWindow = window.open(`../download_material?id=${materialId}`, '_blank');
    
    // Check if download window opened successfully
    if (downloadWindow) {
        // Wait a bit then re-enable button and show success message
        setTimeout(() => {
            downloadBtn.innerHTML = originalText;
            downloadBtn.disabled = false;
            showToast('Download started! Check your downloads folder.', 'success');
            
            // Reload materials list to update download count
            const moduleId = document.getElementById('material_module_id').value;
            if (moduleId) {
                loadModuleMaterials(moduleId);
            }
        }, 2000);
    } else {
        // Popup blocked
        downloadBtn.innerHTML = originalText;
        downloadBtn.disabled = false;
        showToast('Download popup was blocked. Please allow popups and try again.', 'error');
    }
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// Initialize course actions
function initializeCourseActions() {
    console.log('Initializing course actions...');
    
    // Add material form submission handler
    const addMaterialForm = document.getElementById('addMaterialForm');
    if (addMaterialForm) {
        addMaterialForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_material');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Uploading...';
            submitBtn.disabled = true;
            
            // Submit form via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const jsonData = JSON.parse(data);
                    if (jsonData.success) {
                        // Reload materials list
                        const moduleId = document.getElementById('material_module_id').value;
                        loadModuleMaterials(moduleId);
                        
                        // Reset form
                        this.reset();
                        
                        // Show success message
                        showToast('Material uploaded successfully!', 'success');
                    } else {
                        showToast(jsonData.message || 'Failed to upload material', 'error');
                    }
                } catch (e) {
                    // If not JSON, show generic message
                    showToast('Material uploaded successfully!', 'success');
                    
                    // Reload materials list
                    const moduleId = document.getElementById('material_module_id').value;
                    loadModuleMaterials(moduleId);
                    
                    // Reset form
                    this.reset();
                }
            })
            .catch(error => {
                console.error('Error uploading material:', error);
                showToast('Failed to upload material. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}

// File preview functionality for create module form
document.addEventListener('DOMContentLoaded', function() {
    // Initialize course actions
    initializeCourseActions();
    
    // Set up edit material form submission handler
    const editMaterialForm = document.getElementById('editMaterialForm');
    
    if (editMaterialForm) {
        editMaterialForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const formData = new FormData(this);
            formData.append('action', 'update_material');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
            submitBtn.disabled = true;
            
            // Submit form via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const jsonData = JSON.parse(data);
                    if (jsonData.success) {
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('editMaterialModal')).hide();
                        
                        // Show success message
                        showToast('Material updated successfully!', 'success');
                        
                        // Reload materials list
                        const moduleId = document.getElementById('material_module_id').value;
                        loadModuleMaterials(moduleId);
                        
                        // Reset form
                        this.reset();
                    } else {
                        showToast(jsonData.message || 'Failed to update material', 'error');
                    }
                } catch (e) {
                    // If not JSON, show generic message
                    showToast('Material updated successfully!', 'success');
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('editMaterialModal')).hide();
                    
                    // Reload materials list
                    const moduleId = document.getElementById('material_module_id').value;
                    loadModuleMaterials(moduleId);
                    
                    // Reset form
                    this.reset();
                }
            })
            .catch(error => {
                console.error('Error updating material:', error);
                showToast('Failed to update material. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Manual form submission function
    window.submitModuleForm = function() {
        const form = document.getElementById('createModuleForm');
        
        if (!form) {
            return;
        }
        
        // Validate required fields
        const title = form.querySelector('#title').value.trim();
        const description = form.querySelector('#description').value.trim();
        
        if (!title || !description) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitModuleBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Module...';
        submitBtn.disabled = true;
        
        // Submit the form
        form.submit();
    };
    
    const fileInput = document.getElementById('course_files');
    const filePreview = document.getElementById('filePreview');
    const fileList = document.getElementById('fileList');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = this.files;
            
            if (files.length > 0) {
                filePreview.style.display = 'block';
                fileList.innerHTML = '';
                
                Array.from(files).forEach(file => {
                    const fileSize = (file.size / 1024).toFixed(1);
                    const fileType = file.name.split('.').pop().toLowerCase();
                    const iconClass = fileType === 'pdf' ? 'fa-file-pdf' : 'fa-file-word';
                    
                    fileList.innerHTML += `
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>
                                <i class="fas ${iconClass} text-primary me-2"></i>
                                ${file.name}
                            </span>
                            <small class="text-muted">${fileSize} KB</small>
                        </div>
                    `;
                });
            } else {
                filePreview.style.display = 'none';
            }
        });
    }
    
    // Cover image preview for create module form
    const coverInput = document.getElementById('material_cover');
    const coverPreview = document.getElementById('coverPreview');
    const coverImagePreview = document.getElementById('coverImagePreview');
    
    if (coverInput) {
        coverInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverImagePreview.src = e.target.result;
                    coverPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                coverPreview.style.display = 'none';
            }
        });
    }
    
    // Cover image preview for add material form
    const newCoverInput = document.getElementById('new_material_cover');
    const newCoverPreview = document.getElementById('newCoverPreview');
    const newCoverImagePreview = document.getElementById('newCoverImagePreview');
    
    if (newCoverInput) {
        newCoverInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    newCoverImagePreview.src = e.target.result;
                    newCoverPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                newCoverPreview.style.display = 'none';
            }
        });
    }
    

    
    // Cover image preview for edit material form
    const editCoverInput = document.getElementById('edit_material_cover');
    const editCoverPreview = document.getElementById('editCoverPreview');
    const editCoverImagePreview = document.getElementById('editCoverImagePreview');
    
    if (editCoverInput) {
        editCoverInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    editCoverImagePreview.src = e.target.result;
                    editCoverPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                editCoverPreview.style.display = 'none';
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
