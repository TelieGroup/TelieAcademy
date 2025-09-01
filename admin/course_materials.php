<?php
require_once '../config/session.php';
require_once '../includes/Course.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

$course = new Course();

// Get module ID from URL
$moduleId = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;
if (!$moduleId) {
    header('Location: courses.php');
    exit;
}

// Get module details
$moduleData = $course->getModuleById($moduleId);
if (!$moduleData) {
    header('Location: courses.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'upload_material':
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $requiredLessonId = !empty($_POST['required_lesson_id']) ? (int)$_POST['required_lesson_id'] : null;
            $relatedLessonId = !empty($_POST['related_lesson_id']) ? (int)$_POST['related_lesson_id'] : null;
            $orderIndex = isset($_POST['order_index']) ? (int)$_POST['order_index'] : 0;
            
            if (!empty($title) && !empty($_FILES['material_file']['name'])) {
                $file = $_FILES['material_file'];
                $fileName = $file['name'];
                $fileSize = $file['size'];
                $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                
                // Validate file type
                $allowedTypes = ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
                if (!in_array(strtolower($fileType), $allowedTypes)) {
                    $errorMessage = "Invalid file type. Only PDF, PowerPoint, Word, Excel, and Archive files are allowed.";
                    break;
                }
                
                // Validate file size (max 50MB)
                $maxSize = 50 * 1024 * 1024; // 50MB
                if ($fileSize > $maxSize) {
                    $errorMessage = "File size too large. Maximum allowed size is 50MB.";
                    break;
                }
                
                // Create upload directory if it doesn't exist
                $uploadDir = '../uploads/course_materials/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $uniqueName = uniqid() . '_' . $fileName;
                $filePath = $uploadDir . $uniqueName;
                
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $materialId = $course->createMaterial($moduleId, $title, $description, $fileName, $filePath, $fileSize, $fileType, null, null, $requiredLessonId, $relatedLessonId, $orderIndex);
                    if ($materialId) {
                        $successMessage = "Material uploaded successfully with progressive learning integration!";
                    } else {
                        $errorMessage = "Failed to save material information.";
                        unlink($filePath); // Clean up uploaded file
                    }
                } else {
                    $errorMessage = "Failed to upload file.";
                }
            } else {
                $errorMessage = "Please fill in all required fields and select a file.";
            }
            break;
            
        case 'update_material':
            $id = $_POST['material_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            if (!empty($title) && !empty($description)) {
                if ($course->updateMaterial($id, $title, $description, $isActive)) {
                    $successMessage = "Material updated successfully!";
                } else {
                    $errorMessage = "Failed to update material.";
                }
            } else {
                $errorMessage = "Please fill in all required fields.";
            }
            break;
            
        case 'delete_material':
            $id = $_POST['material_id'];
            if ($course->deleteMaterial($id)) {
                $successMessage = "Material deleted successfully!";
            } else {
                $errorMessage = "Failed to delete material.";
            }
            break;
    }
}

// Get materials for this module
$materials = $course->getMaterialsByModule($moduleId, false);

// Get course info and posts for lesson selection
$courseData = $course->getCourseById($moduleData['course_id']);
$coursePosts = $course->getPostsByModule($moduleId, false); // Get all posts in this module for selection

include '../includes/head.php';
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                    <li class="breadcrumb-item"><a href="course_modules.php?course_id=<?php echo $moduleData['course_id']; ?>"><?php echo htmlspecialchars($moduleData['course_title']); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($moduleData['title']); ?> - Materials</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-alt text-primary"></i>
                        <?php echo htmlspecialchars($moduleData['title']); ?> - Course Materials
                    </h1>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($moduleData['description']); ?>
                        <br><small>Course: <?php echo htmlspecialchars($moduleData['course_title']); ?></small>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="course_modules.php?course_id=<?php echo $moduleData['course_id']; ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Modules
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">
                        <i class="fas fa-upload me-2"></i>Upload Material
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

            <!-- Materials List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Course Materials
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($materials)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <p>No materials found for this module. Upload your first material to get started!</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($materials as $material): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="file-type-badge">
                                                    <i class="fas fa-file-<?php echo strtolower($material['file_type']) === 'pdf' ? 'pdf' : 'word'; ?> text-primary"></i>
                                                    <span class="badge bg-secondary ms-1"><?php echo strtoupper($material['file_type']); ?></span>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="editMaterial(<?php echo $material['id']; ?>, '<?php echo htmlspecialchars($material['title']); ?>', '<?php echo htmlspecialchars($material['description']); ?>', <?php echo $material['is_active'] ? 'true' : 'false'; ?>)">
                                                            <i class="fas fa-edit me-2"></i>Edit
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="deleteMaterial(<?php echo $material['id']; ?>, '<?php echo htmlspecialchars($material['title']); ?>')">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            
                                            <h6 class="card-title"><?php echo htmlspecialchars($material['title']); ?></h6>
                                            <p class="card-text text-muted small"><?php echo htmlspecialchars($material['description']); ?></p>
                                            
                                            <!-- Progressive Learning Info -->
                                            <?php if (!empty($material['required_lesson_id']) || !empty($material['related_lesson_id'])): ?>
                                                <div class="progressive-learning-info mb-2">
                                                    <?php if (!empty($material['required_lesson_id'])): ?>
                                                        <div class="mb-1">
                                                            <span class="badge bg-warning text-dark">
                                                                <i class="fas fa-lock me-1"></i>Requires Lesson Completion
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($material['related_lesson_id'])): ?>
                                                        <div class="mb-1">
                                                            <span class="badge bg-info">
                                                                <i class="fas fa-link me-1"></i>Related to Lesson
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="material-info">
                                                <div class="row text-muted small">
                                                    <div class="col-6">
                                                        <i class="fas fa-download me-1"></i>
                                                        <?php echo number_format($material['download_count']); ?> downloads
                                                    </div>
                                                    <div class="col-6">
                                                        <i class="fas fa-sort-numeric-down me-1"></i>
                                                        Order: <?php echo $material['order_index']; ?>
                                                    </div>
                                                </div>
                                                <div class="row text-muted small mt-1">
                                                    <div class="col-12">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo date('M j, Y', strtotime($material['created_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <span class="badge <?php echo $material['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $material['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                                <small class="text-muted ms-2"><?php echo number_format($material['file_size'] / 1024, 1); ?> KB</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Material Modal -->
<div class="modal fade" id="uploadMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Course Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload_material">
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>Basic Information
                            </h6>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Material Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order_index" class="form-label">Display Order</label>
                        <input type="number" class="form-control" id="order_index" name="order_index" 
                               value="0" min="0" max="999">
                        <div class="form-text">Lower numbers appear first (0 = highest priority)</div>
                    </div>
                    
                    <!-- Progressive Learning Integration -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-success mb-3">
                                <i class="fas fa-graduation-cap me-2"></i>Progressive Learning Integration
                            </h6>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="required_lesson_id" class="form-label">Required Lesson (Optional)</label>
                        <select class="form-select" id="required_lesson_id" name="required_lesson_id">
                            <option value="">No requirement - Always accessible</option>
                            <?php if (!empty($coursePosts)): ?>
                                <?php foreach ($coursePosts as $post): ?>
                                    <option value="<?php echo $post['id']; ?>">
                                        <?php echo htmlspecialchars($post['title']); ?> 
                                        (Lesson <?php echo $post['lesson_order']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Users must complete this lesson before accessing the material</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="related_lesson_id" class="form-label">Related Lesson (Optional)</label>
                        <select class="form-select" id="related_lesson_id" name="related_lesson_id">
                            <option value="">Not related to a specific lesson</option>
                            <?php if (!empty($coursePosts)): ?>
                                <?php foreach ($coursePosts as $post): ?>
                                    <option value="<?php echo $post['id']; ?>">
                                        <?php echo htmlspecialchars($post['title']); ?> 
                                        (Lesson <?php echo $post['lesson_order']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Material will appear in the lesson page and be recommended to users</div>
                    </div>
                    
                    <!-- File Upload -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-warning mb-3">
                                <i class="fas fa-upload me-2"></i>File Upload
                            </h6>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="material_file" class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="material_file" name="material_file" 
                               accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.zip,.rar" required>
                        <div class="form-text">
                            <strong>Allowed types:</strong> PDF, PowerPoint, Word, Excel, Archives<br>
                            <strong>Maximum size:</strong> 50MB
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Material</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Material Modal -->
<div class="modal fade" id="editMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_material">
                    <input type="hidden" name="material_id" id="edit_material_id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Material Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
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
                    <button type="submit" class="btn btn-primary">Update Material</button>
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
                <p class="text-danger small">This action cannot be undone and the file will be permanently removed.</p>
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

<script>
function editMaterial(id, title, description, isActive) {
    document.getElementById('edit_material_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_is_active').checked = isActive;
    
    new bootstrap.Modal(document.getElementById('editMaterialModal')).show();
}

function deleteMaterial(id, title) {
    document.getElementById('deleteItemName').textContent = title;
    document.getElementById('deleteForm').innerHTML = `
        <input type="hidden" name="action" value="delete_material">
        <input type="hidden" name="material_id" value="${id}">
        <button type="submit" class="btn btn-danger">Delete</button>
    `;
    
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
