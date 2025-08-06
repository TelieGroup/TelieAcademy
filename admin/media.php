<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Media.php';

$user = new User();
$media = new Media();

// Check if user is logged in and is admin
if (!$user->isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$currentUser = $user->getCurrentUser();
if (!$currentUser || !$currentUser['is_premium']) {
    header('Location: ../index.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle AJAX requests
if ($action === 'get_media') {
    header('Content-Type: application/json');
    try {
        $mediaFiles = $media->getAllMedia();
        echo json_encode([
            'success' => true,
            'media' => $mediaFiles
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? $action;
    
    if ($postAction === 'upload_media' && isset($_FILES['media_file'])) {
        try {
            $uploadDir = '../uploads/';
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'text/plain'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            
            $file = $_FILES['media_file'];
            $fileName = $file['name'];
            $fileType = $file['type'];
            $fileSize = $file['size'];
            $fileTmp = $file['tmp_name'];
            
            // Validate file
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Invalid file type. Allowed: JPG, PNG, GIF, WebP, PDF, TXT');
            }
            
            if ($fileSize > $maxFileSize) {
                throw new Exception('File too large. Maximum size: 5MB');
            }
            
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $uniqueFileName;
            
            // Move uploaded file
            if (move_uploaded_file($fileTmp, $filePath)) {
                $mediaData = [
                    'filename' => $uniqueFileName,
                    'original_name' => $fileName,
                    'file_type' => $fileType,
                    'file_size' => $fileSize,
                    'file_path' => 'uploads/' . $uniqueFileName,
                    'uploaded_by' => $currentUser['id'],
                    'description' => $_POST['description'] ?? '',
                    'alt_text' => $_POST['alt_text'] ?? ''
                ];
                
                $result = $media->uploadMedia($mediaData);
                if ($result['success']) {
                    $successMessage = 'Media uploaded successfully!';
                } else {
                    throw new Exception($result['message']);
                }
            } else {
                throw new Exception('Failed to upload file');
            }
        } catch (Exception $e) {
            $errorMessage = 'Error uploading media: ' . $e->getMessage();
        }
    } elseif ($postAction === 'delete_media' && isset($_POST['media_id'])) {
        try {
            $mediaId = $_POST['media_id'];
            $result = $media->deleteMedia($mediaId);
            if ($result['success']) {
                $successMessage = 'Media deleted successfully!';
            } else {
                $errorMessage = $result['message'];
            }
        } catch (Exception $e) {
            $errorMessage = 'Error deleting media: ' . $e->getMessage();
        }
    } elseif ($postAction === 'update_media' && isset($_POST['media_id'])) {
        try {
            $mediaId = $_POST['media_id'];
            $updateData = [
                'description' => $_POST['description'] ?? '',
                'alt_text' => $_POST['alt_text'] ?? '',
                'tags' => $_POST['tags'] ?? ''
            ];
            
            $result = $media->updateMedia($mediaId, $updateData);
            if ($result['success']) {
                $successMessage = 'Media updated successfully!';
            } else {
                $errorMessage = $result['message'];
            }
        } catch (Exception $e) {
            $errorMessage = 'Error updating media: ' . $e->getMessage();
        }
    }
}

// Set page variables for head component
$pageTitle = 'Media Management';
$pageDescription = 'Upload, organize, and manage media files';

include '../includes/head.php';
?>
<!-- Admin CSS -->
<link rel="stylesheet" href="admin.css">
<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if (isset($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Media Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                        <i class="fas fa-upload me-1"></i>Upload Media
                    </button>
                </div>
            </div>

            <!-- Media Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Files
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $media->getTotalMediaCount(); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-images fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Size
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $media->formatFileSize($media->getTotalMediaSize()); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hdd fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Images
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $media->getMediaCountByType('image'); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-image fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Documents
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $media->getMediaCountByType('document'); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Grid -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Media Library</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $allMedia = $media->getAllMedia();
                            ?>
                            <div class="row" id="mediaGrid">
                                <?php if (empty($allMedia)): ?>
                                <div class="col-12 text-center py-5">
                                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                    <h3 class="text-muted">No media files found</h3>
                                    <p class="text-muted">Upload your first media file to get started.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                                        <i class="fas fa-upload me-1"></i>Upload Media
                                    </button>
                                </div>
                                <?php else: ?>
                                <?php foreach ($allMedia as $mediaItem): ?>
                                <div class="col-md-3 col-lg-2 mb-4">
                                    <div class="media-item card h-100">
                                        <div class="media-preview">
                                            <?php if (strpos($mediaItem['file_type'], 'image/') === 0): ?>
                                            <img src="../<?php echo htmlspecialchars($mediaItem['file_path']); ?>" 
                                                 class="card-img-top" alt="<?php echo htmlspecialchars($mediaItem['alt_text']); ?>"
                                                 style="height: 150px; object-fit: cover;">
                                            <?php else: ?>
                                            <div class="file-preview d-flex align-items-center justify-content-center" 
                                                 style="height: 150px; background-color: #f8f9fa;">
                                                <i class="fas fa-file fa-3x text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body p-2">
                                            <h6 class="card-title small mb-1" title="<?php echo htmlspecialchars($mediaItem['original_name']); ?>">
                                                <?php echo htmlspecialchars(substr($mediaItem['original_name'], 0, 20)); ?>
                                                <?php if (strlen($mediaItem['original_name']) > 20): ?>...<?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted mb-1">
                                                <?php echo $media->formatFileSize($mediaItem['file_size']); ?>
                                            </p>
                                            <p class="card-text small text-muted mb-2">
                                                <?php echo date('M j, Y', strtotime($mediaItem['uploaded_at'])); ?>
                                            </p>
                                            <div class="btn-group btn-group-sm w-100">
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        onclick="copyMediaUrl('<?php echo htmlspecialchars($mediaItem['file_path']); ?>')"
                                                        title="Copy URL">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                                <button class="btn btn-outline-info btn-sm" 
                                                        onclick="editMedia(<?php echo $mediaItem['id']; ?>)"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteMedia(<?php echo $mediaItem['id']; ?>, '<?php echo htmlspecialchars($mediaItem['original_name']); ?>')"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Upload Media Modal -->
<div class="modal fade" id="uploadMediaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_media">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="media_file" class="form-label">Select File</label>
                        <input type="file" class="form-control" id="media_file" name="media_file" required>
                        <div class="form-text">Allowed types: JPG, PNG, GIF, WebP, PDF, TXT (Max: 5MB)</div>
                    </div>
                    <div class="mb-3">
                        <label for="alt_text" class="form-label">Alt Text (for images)</label>
                        <input type="text" class="form-control" id="alt_text" name="alt_text" placeholder="Describe the image for accessibility">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Media Modal -->
<div class="modal fade" id="editMediaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_media">
                <input type="hidden" name="media_id" id="edit_media_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_alt_text" class="form-label">Alt Text</label>
                        <input type="text" class="form-control" id="edit_alt_text" name="alt_text">
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="edit_tags" name="tags" placeholder="Comma-separated tags">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Copy media URL to clipboard
function copyMediaUrl(filePath) {
    const url = window.location.origin + '/TelieAcademy/' + filePath;
    navigator.clipboard.writeText(url).then(() => {
        alert('Media URL copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Media URL copied to clipboard!');
    });
}

// Edit media
function editMedia(mediaId) {
    // This would typically fetch media data via AJAX
    // For now, we'll just show the modal
    document.getElementById('edit_media_id').value = mediaId;
    const modal = new bootstrap.Modal(document.getElementById('editMediaModal'));
    modal.show();
}

// Delete media
function deleteMedia(mediaId, fileName) {
    if (confirm('Are you sure you want to delete "' + fileName + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'media.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_media';
        
        const mediaIdInput = document.createElement('input');
        mediaIdInput.type = 'hidden';
        mediaIdInput.name = 'media_id';
        mediaIdInput.value = mediaId;
        
        form.appendChild(actionInput);
        form.appendChild(mediaIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?> 