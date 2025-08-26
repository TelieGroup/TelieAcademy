<?php
require_once '../config/session.php';
require_once '../includes/Course.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

$course = new Course();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_course':
            $title = trim($_POST['title']);
            $slug = strtolower(str_replace(' ', '-', $title));
            $description = trim($_POST['description']);
            $thumbnail = null;
            
            if (!empty($title) && !empty($description)) {
                // Handle cover image upload
                if (isset($_FILES['course_cover']) && $_FILES['course_cover']['error'] === UPLOAD_ERR_OK) {
                    $coverFileName = $_FILES['course_cover']['name'];
                    $coverFileType = strtolower(pathinfo($coverFileName, PATHINFO_EXTENSION));
                    
                    // Validate image type
                    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    if (in_array($coverFileType, $allowedImageTypes)) {
                        // Create uploads directory for course covers if it doesn't exist
                        $imageUploadDir = '../uploads/course_covers/';
                        if (!is_dir($imageUploadDir)) {
                            mkdir($imageUploadDir, 0755, true);
                        }
                        
                        // Generate unique filename for cover image
                        $uniqueCoverName = uniqid() . '_cover_' . $coverFileName;
                        $thumbnail = $imageUploadDir . $uniqueCoverName;
                        
                        if (!move_uploaded_file($_FILES['course_cover']['tmp_name'], $thumbnail)) {
                            $errorMessage = "Failed to upload cover image.";
                            break;
                        }
                    } else {
                        $errorMessage = "Invalid image type. Please use JPG, PNG, or GIF.";
                        break;
                    }
                }
                
                $courseId = $course->createCourse($title, $slug, $description, $thumbnail);
                if ($courseId) {
                    $successMessage = "Course created successfully!";
                } else {
                    $errorMessage = "Failed to create course.";
                }
            } else {
                $errorMessage = "Please fill in all required fields.";
            }
            break;
            
        case 'update_course':
            $courseId = (int)$_POST['course_id'];
            $title = trim($_POST['title']);
            $slug = strtolower(str_replace(' ', '-', $title));
            $description = trim($_POST['description']);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            if (!empty($title) && !empty($description) && $courseId > 0) {
                // Get current course to check if cover image needs updating
                $currentCourse = $course->getCourseById($courseId);
                
                if ($currentCourse) {
                    $thumbnail = $currentCourse['thumbnail'];
                    
                    // Handle new cover image if uploaded
                    if (isset($_FILES['course_cover']) && $_FILES['course_cover']['error'] === UPLOAD_ERR_OK) {
                        $coverFileName = $_FILES['course_cover']['name'];
                        $coverFileType = strtolower(pathinfo($coverFileName, PATHINFO_EXTENSION));
                        
                        // Validate image type
                        $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
                        if (in_array($coverFileType, $allowedImageTypes)) {
                            // Create uploads directory for course covers if it doesn't exist
                            $imageUploadDir = '../uploads/course_covers/';
                            if (!is_dir($imageUploadDir)) {
                                mkdir($imageUploadDir, 0755, true);
                            }
                            
                            // Generate unique filename for cover image
                            $uniqueCoverName = uniqid() . '_cover_' . $coverFileName;
                            $newThumbnail = $imageUploadDir . $uniqueCoverName;
                            
                            if (move_uploaded_file($_FILES['course_cover']['tmp_name'], $newThumbnail)) {
                                // Delete old cover image if it exists
                                if ($thumbnail && file_exists($thumbnail)) {
                                    unlink($thumbnail);
                                }
                                
                                $thumbnail = $newThumbnail;
                            }
                        } else {
                            $errorMessage = "Invalid image type. Please use JPG, PNG, or GIF.";
                            break;
                        }
                    }
                    
                    if ($course->updateCourse($courseId, $title, $slug, $description, $thumbnail, $isActive)) {
                        $successMessage = "Course updated successfully!";
                    } else {
                        $errorMessage = "Failed to update course.";
                    }
                } else {
                    $errorMessage = "Course not found.";
                }
            } else {
                $errorMessage = "Please fill in all required fields.";
            }
            break;
            
        case 'delete_course':
            $courseId = (int)$_POST['course_id'];
            if ($courseId > 0) {
                if ($course->deleteCourse($courseId)) {
                    $successMessage = "Course deleted successfully!";
                } else {
                    $errorMessage = "Failed to delete course.";
                }
            } else {
                $errorMessage = "Invalid course ID.";
            }
            break;
    }
}

// Get all courses for display
$allCourses = $course->getAllCourses(false);
$courseStats = $course->getCourseStatistics();

include '../includes/head.php';
?>

<style>
/* Ensure modal is properly positioned and interactive */
#createCourseModal {
    z-index: 1050 !important;
}

#createCourseModal .modal-dialog {
    z-index: 1055 !important;
}

/* Fix modal backdrop and positioning */
.modal-backdrop {
    z-index: 1040 !important;
}

/* Ensure modal content is clickable */
.modal-content {
    position: relative;
    z-index: 1055 !important;
}

/* Debug styles */
.modal.show {
    display: block !important;
}

.modal-open {
    overflow: hidden;
}

/* Force modal to be on top */
.modal {
    z-index: 1050 !important;
}

/* Dark Mode Support */
.dark-mode .container-fluid {
    background: #1a1a1a;
    color: #e0e0e0;
}

.dark-mode .card {
    background: #2d2d2d;
    border-color: #404040;
    color: #e0e0e0;
}

.dark-mode .card.bg-primary {
    background: #0d6efd !important;
    color: white !important;
}

.dark-mode .card.bg-success {
    background: #198754 !important;
    color: white !important;
}

.dark-mode .card.bg-info {
    background: #0dcaf0 !important;
    color: white !important;
}

.dark-mode .card.bg-warning {
    background: #ffc107 !important;
    color: #212529 !important;
}

.dark-mode .table {
    background: #2d2d2d;
    color: #e0e0e0;
}

.dark-mode .table th {
    background: #353535;
    color: #ffffff;
    border-color: #404040;
}

.dark-mode .table td {
    border-color: #404040;
    color: #e0e0e0;
}

.dark-mode .table tbody tr:hover {
    background: #353535;
}

.dark-mode .form-control,
.dark-mode .form-select {
    background: #404040;
    border-color: #505050;
    color: #e0e0e0;
}

.dark-mode .form-control:focus,
.dark-mode .form-select:focus {
    background: #404040;
    border-color: #0d6efd;
    color: #e0e0e0;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.dark-mode .form-label {
    color: #e0e0e0;
}

.dark-mode .modal-content {
    background: #2d2d2d;
    color: #e0e0e0;
    border: 1px solid #404040;
}

.dark-mode .modal-header {
    background: #353535;
    border-bottom-color: #404040;
}

.dark-mode .modal-footer {
    background: #353535;
    border-top-color: #404040;
}

.dark-mode .alert {
    background: #2d2d2d;
    border-color: #404040;
    color: #e0e0e0;
}

.dark-mode .alert-success {
    background: #1e4a1e;
    border-color: #2d5a2d;
    color: #d4edda;
}

.dark-mode .alert-danger {
    background: #4a1e1e;
    border-color: #5a2d2d;
    color: #f8d7da;
}
</style>

<?php include '../includes/header.php'; ?>

<!-- Admin CSS -->
<link rel="stylesheet" href="admin.css">

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-graduation-cap text-primary"></i>
                    Course Management
                    <?php
                    try {
                        $totalCourses = $courseStats['total_courses'] ?? 0;
                        $totalModules = $courseStats['total_modules'] ?? 0;
                        $totalMaterials = $courseStats['total_materials'] ?? 0;
                        if ($totalCourses > 0):
                        ?>
                        <span class="badge bg-info ms-2"><?php echo $totalCourses; ?> Courses</span>
                        <?php endif; ?>
                        <?php if ($totalModules > 0): ?>
                        <span class="badge bg-success ms-2"><?php echo $totalModules; ?> Modules</span>
                        <?php endif; ?>
                        <?php if ($totalMaterials > 0): ?>
                        <span class="badge bg-warning text-dark ms-2"><?php echo $totalMaterials; ?> Materials</span>
                        <?php endif; ?>
                    <?php } catch (Exception $e) { /* Silently fail */ } ?>
                </h1>
                <button class="btn btn-primary" type="button" onclick="openCreateCourseModal()">
                    <i class="fas fa-plus me-2"></i>Add New Course
                </button>
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $courseStats['total_courses'] ?? 0; ?></h4>
                                    <p class="mb-0">Total Courses</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-graduation-cap fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $courseStats['total_modules'] ?? 0; ?></h4>
                                    <p class="mb-0">Total Modules</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-book fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $courseStats['total_materials'] ?? 0; ?></h4>
                                    <p class="mb-0">Total Materials</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $courseStats['total_downloads'] ?? 0; ?></h4>
                                    <p class="mb-0">Total Downloads</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-download fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>All Courses
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($allCourses)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                            <p>No courses found. Create your first course to get started!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Cover</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allCourses as $courseItem): ?>
                                        <tr>
                                            <td>
                                                <?php if ($courseItem['thumbnail']): ?>
                                                    <img src="<?php echo htmlspecialchars($courseItem['thumbnail']); ?>" 
                                                         alt="Course Cover" 
                                                         class="img-thumbnail" 
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($courseItem['title']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($courseItem['slug']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($courseItem['description'], 0, 100)) . (strlen($courseItem['description']) > 100 ? '...' : ''); ?></td>
                                                                                                <td>
                                                        <span class="badge <?php echo $courseItem['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                            <?php echo $courseItem['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                        <br><small class="text-muted">ID: <?php echo $courseItem['id']; ?></small>
                                                    </td>
                                            <td><?php echo date('M j, Y', strtotime($courseItem['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="course_modules.php?course_id=<?php echo $courseItem['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Manage Modules">
                                                        <i class="fas fa-book"></i>
                                                    </a>
                                                    <button class="btn btn-outline-info" onclick="editCourse(<?php echo $courseItem['id']; ?>, '<?php echo htmlspecialchars($courseItem['title']); ?>', '<?php echo htmlspecialchars($courseItem['description']); ?>', <?php echo $courseItem['is_active'] ? 'true' : 'false'; ?>, '<?php echo htmlspecialchars($courseItem['thumbnail'] ?? ''); ?>')" title="Edit Course">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteCourse(<?php echo $courseItem['id']; ?>, '<?php echo htmlspecialchars($courseItem['title']); ?>')" title="Delete Course">
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

<!-- Create Course Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1" aria-labelledby="createCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCourseModalLabel">Create New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_course">
                    <div class="mb-3">
                        <label for="title" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="course_cover" class="form-label">Course Cover Image (Optional)</label>
                        <input type="file" class="form-control" id="course_cover" name="course_cover" accept="image/*">
                        <div class="form-text">JPG, PNG, GIF (Max: 2MB). This will be displayed as the course thumbnail.</div>
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
                    <button type="submit" class="btn btn-primary">Create Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_course">
                    <input type="hidden" name="course_id" id="edit_course_id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_course_cover" class="form-label">Update Cover Image (Optional)</label>
                        <input type="file" class="form-control" id="edit_course_cover" name="course_cover" accept="image/*">
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
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" checked>
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone and will also delete all associated modules and materials.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Open create course modal function
function openCreateCourseModal() {
    console.log('Open create course modal function called');
    const modalElement = document.getElementById('createCourseModal');
    if (modalElement) {
        console.log('Modal element found, trying to show...');
        try {
            // Create modal with proper options
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            
            // Show the modal
            modal.show();
            console.log('Create course modal shown successfully');
            
            // Ensure modal is properly positioned and interactive
            setTimeout(() => {
                modalElement.style.zIndex = '1050';
                const dialog = modalElement.querySelector('.modal-dialog');
                if (dialog) {
                    dialog.style.zIndex = '1055';
                }
                
                // Test if form elements are accessible
                const titleInput = modalElement.querySelector('#title');
                const descriptionTextarea = modalElement.querySelector('#description');
                
                if (titleInput) {
                    titleInput.focus();
                    console.log('Title input is accessible and focused');
                }
                
                if (descriptionTextarea) {
                    console.log('Description textarea is accessible');
                }
                
                // Log modal state
                console.log('Modal z-index:', modalElement.style.zIndex);
                console.log('Modal display:', modalElement.style.display);
                console.log('Modal classes:', modalElement.className);
            }, 100);
            
        } catch (error) {
            console.error('Error showing create course modal:', error);
            // Fallback: try to show modal manually
            modalElement.style.display = 'block';
            modalElement.style.zIndex = '1050';
            modalElement.classList.add('show');
            document.body.classList.add('modal-open');
        }
    } else {
        console.error('Create course modal element not found');
    }
}



// Ensure modal functionality works
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing course management...');
    
    // Debug: Check if modal element exists
    const modal = document.getElementById('createCourseModal');
    if (modal) {
        console.log('Modal found:', modal);
    } else {
        console.error('Modal not found!');
    }
    
    // Debug: Check if Bootstrap is loaded
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap loaded successfully, version:', bootstrap.VERSION || 'unknown');
    } else {
        console.error('Bootstrap not loaded!');
    }
    
    // Add form submission handler
    const createForm = document.querySelector('#createCourseModal form');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            console.log('Form submitted');
            // Form will submit normally
        });
    }
    
    // Test modal interactivity
    const modalElement = document.getElementById('createCourseModal');
    if (modalElement) {
        modalElement.addEventListener('click', function(e) {
            console.log('Modal clicked at:', e.target.tagName, e.target.className);
        });
        
        // Test form elements
        const titleInput = modalElement.querySelector('#title');
        if (titleInput) {
            titleInput.addEventListener('focus', function() {
                console.log('Title input focused');
            });
            titleInput.addEventListener('input', function() {
                console.log('Title input value:', this.value);
            });
        }
    }
    
    // Initialize edit and delete functionality
    initializeCourseActions();
});

// Edit course function
function editCourse(id, title, description, isActive, thumbnail = null) {
    console.log('Editing course:', id, title, description, isActive, thumbnail);
    
    // Populate the edit modal
    document.getElementById('edit_course_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_is_active').checked = isActive;
    
    // Display current cover image
    const currentCoverDisplay = document.getElementById('currentCoverDisplay');
    if (thumbnail) {
        currentCoverDisplay.innerHTML = `
            <img src="${thumbnail}" alt="Current Cover" class="img-fluid" style="max-height: 150px; border-radius: 4px;">
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
    document.getElementById('edit_course_cover').value = '';
    
    // Show the edit modal
    const modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    modal.show();
}

// Delete course function
function deleteCourse(id, title) {
    console.log('Deleting course:', id, title);
    
    // Set the delete form values
    document.getElementById('deleteItemName').textContent = title;
    document.getElementById('deleteForm').innerHTML = `
        <input type="hidden" name="action" value="delete_course">
        <input type="hidden" name="course_id" value="${id}">
        <button type="submit" class="btn btn-danger">Delete</button>
    `;
    
    // Show the delete confirmation modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

// Cover image preview functionality
document.addEventListener('DOMContentLoaded', function() {
    // Cover image preview for create course form
    const coverInput = document.getElementById('course_cover');
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
    
    // Cover image preview for edit course form
    const editCoverInput = document.getElementById('edit_course_cover');
    const editCoverPreview = document.getElementById('editCoverPreview');
    const editCoverImagePreview = document.getElementById('editCoverImagePreview');
    
    if (editCoverInput) {
        editCoverInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(e) {
                    editCoverImagePreview.src = e.target.result;
                    editCoverPreview.style.display = 'block';
                };
            } else {
                editCoverPreview.style.display = 'none';
            }
        });
    }
});

// Initialize course actions
function initializeCourseActions() {
    console.log('Initializing course actions...');
    
    // Add form submission handlers
    const editForm = document.querySelector('#editCourseModal form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            console.log('Edit form submitted');
        });
    }
    
    const deleteForm = document.querySelector('#deleteConfirmModal form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            console.log('Delete form submitted');
        });
    }
    
    // Add material form submission handler
    const addMaterialForm = document.getElementById('addMaterialForm');
    if (addMaterialForm) {
        addMaterialForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_material');
            
            // Submit form via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log('Material added successfully');
                // Reload materials list
                const moduleId = document.getElementById('material_module_id').value;
                loadModuleMaterials(moduleId);
                
                // Reset form
                this.reset();
                
                // Show success message
                alert('Material uploaded successfully!');
            })
            .catch(error => {
                console.error('Error uploading material:', error);
                alert('Failed to upload material. Please try again.');
            });
        });
    }
}
</script>
