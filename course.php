<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

// Get course slug from URL
$courseSlug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (!$courseSlug) {
    header('Location: courses.php');
    exit;
}

// Get course details
$courseData = $course->getCourseBySlug($courseSlug);
if (!$courseData) {
    header('Location: courses.php');
    exit;
}

// Get modules for this course
$modules = $course->getModulesByCourse($courseData['id'], true);

// Set page variables for head component
$pageTitle = $courseData['title'] . " - Telie Academy";
$pageDescription = $courseData['description'];

include 'includes/head.php';
?>

<style>
/* Enhanced Course Page Styling */

/* Course Header Styling */
.course-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 3rem 0;
    border-radius: 16px;
    margin-bottom: 3rem;
}

/* Material Card Styling */
.material-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
}

.material-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.material-card .card-img-top {
    border-bottom: 1px solid #e9ecef;
    transition: transform 0.3s ease;
}

.material-card:hover .card-img-top {
    transform: scale(1.05);
}

.material-card .file-type-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.material-card .card-title {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.1rem;
}

.material-card .card-text {
    color: #6c757d;
    line-height: 1.5;
    font-size: 0.9rem;
}

.material-card .btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    border-radius: 8px;
    padding: 0.6rem 1.2rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.material-card .btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

/* Accordion Styling */
.accordion-item {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    margin-bottom: 1rem;
    overflow: hidden;
}

.accordion-button {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    font-weight: 600;
    color: #2c3e50;
}

.accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: #007bff;
}

/* Responsive Design */
@media (max-width: 768px) {
    .course-header {
        padding: 2rem 0;
    }
    
    .course-cover img {
        max-height: 200px;
    }
    
    .material-card .card-body {
        padding: 1rem;
    }
}
</style>

<?php include 'includes/header.php'; ?>

<!-- Reading Progress Bar -->
<div class="reading-progress-bar" id="readingProgressBar"></div>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="courses">Course Materials</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($courseData['title']); ?></li>
                </ol>
            </nav>

            <!-- Course Header -->
            <div class="course-header text-center mb-5">
                <h1 class="display-4 mb-3"><?php echo htmlspecialchars($courseData['title']); ?></h1>
                <p class="lead text-muted mb-4"><?php echo htmlspecialchars($courseData['description']); ?></p>
                
                <?php if (!$isLoggedIn): ?>
                    <div class="alert alert-info d-inline-block">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Login Required:</strong> Please login to access course materials
                    </div>
                    <br>
                    <button type="button" class="btn btn-primary btn-lg me-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                <?php elseif (!$isPremium): ?>
                    <div class="alert alert-warning d-inline-block">
                        <i class="fas fa-crown me-2"></i>
                        <strong>Premium Required:</strong> Upgrade to access course materials
                    </div>
                    <br>
                    <a href="#" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#newsletterModal">
                        <i class="fas fa-crown me-2"></i>Upgrade to Premium
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($isPremium): ?>
                <!-- Premium User Content -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <?php if (empty($modules)): ?>
                            <div class="card text-center py-5">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <h3>No Modules Available</h3>
                                <p class="text-muted">This course doesn't have any modules yet. Check back later!</p>
                            </div>
                        <?php else: ?>
                            <!-- Course Modules -->
                            <div class="accordion" id="courseModules">
                                <?php foreach ($modules as $index => $module): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo $module['id']; ?>">
                                            <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapse<?php echo $module['id']; ?>" 
                                                    aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                                    aria-controls="collapse<?php echo $module['id']; ?>">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-3"><?php echo $module['order_index']; ?></span>
                                                    <strong><?php echo htmlspecialchars($module['title']); ?></strong>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo $module['id']; ?>" 
                                             class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                             aria-labelledby="heading<?php echo $module['id']; ?>" 
                                             data-bs-parent="#courseModules">
                                            <div class="accordion-body">
                                                <p class="text-muted mb-3"><?php echo htmlspecialchars($module['description']); ?></p>
                                                
                                                <?php
                                                // Get materials for this module
                                                $materials = $course->getMaterialsByModule($module['id'], true);
                                                if (!empty($materials)):
                                                ?>
                                                    <div class="row">
                                                        <?php foreach ($materials as $material): ?>
                                                            <div class="col-md-6 mb-3">
                                                                <div class="card h-100 material-card">
                                                                    <?php if (!empty($material['cover_image_path'])): ?>
                                                                        <img src="<?php echo str_replace('../', '', $material['cover_image_path']); ?>" 
                                                                             class="card-img-top" alt="Material Cover" 
                                                                             style="height: 150px; object-fit: cover;">
                                                                    <?php endif; ?>
                                                                    <div class="card-body">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <div class="file-type-badge">
                                                                                <i class="fas fa-file-<?php echo strtolower($material['file_type']) === 'pdf' ? 'pdf' : 'word'; ?> text-primary"></i>
                                                                                <span class="badge bg-secondary ms-1"><?php echo strtoupper($material['file_type']); ?></span>
                                                                            </div>
                                                                            <small class="text-muted"><?php echo number_format($material['file_size'] / 1024, 1); ?> KB</small>
                                                                        </div>
                                                                        
                                                                        <h6 class="card-title"><?php echo htmlspecialchars($material['title']); ?></h6>
                                                                        <p class="card-text text-muted small"><?php echo htmlspecialchars($material['description']); ?></p>
                                                                        
                                                                                                                <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted small">
                                                <div class="mb-1">
                                                    <i class="fas fa-download me-1"></i>
                                                    <?php echo $material['download_count'] ?? 0; ?> downloads
                                                </div>
                                                <div class="mb-1">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <?php echo $material['preview_count'] ?? 0; ?> previews
                                                </div>
                                                <div>
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('M j, Y', strtotime($material['created_at'])); ?>
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="preview_material?id=<?php echo $material['id']; ?>" 
                                                   class="btn btn-success btn-sm"
                                                   title="Preview Online"
                                                   target="_blank">
                                                    <i class="fas fa-eye me-1"></i>Preview
                                                </a>
                                                <a href="download_material?id=<?php echo $material['id']; ?>" 
                                                   class="btn btn-primary btn-sm download-btn"
                                                   onclick="confirmDownload(event, '<?php echo htmlspecialchars($material['title']); ?>')"
                                                   title="Download">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center text-muted py-3">
                                                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                                                        <p>No materials available for this module yet.</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Non-Premium User Content -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-lock fa-4x text-muted mb-4"></i>
                                <h3>Premium Content Locked</h3>
                                <p class="text-muted mb-4">
                                    This course contains premium learning materials including PDFs, PowerPoint presentations, 
                                    and comprehensive study guides. Upgrade to premium to unlock access to all resources.
                                </p>
                                
                                <?php if ($isLoggedIn): ?>
                                    <a href="index?premium_required=1" class="btn btn-warning btn-lg">
                                        <i class="fas fa-crown me-2"></i>Upgrade to Premium
                                    </a>
                                <?php else: ?>
                                    <div class="d-flex gap-3 justify-content-center">
                                        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                                            <i class="fas fa-user-plus me-2"></i>Register
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Enhanced download button styles */
.download-btn {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.download-btn:active {
    transform: translateY(0);
}

.download-btn.downloading {
    pointer-events: none;
    opacity: 0.7;
}

/* Material card enhancements */
.material-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.material-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.material-card .card-body {
    padding: 1rem;
}

/* File type badge enhancements */
.file-type-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.file-type-badge .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

/* Button group enhancements */
.btn-group-sm .btn {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
}

.btn-group-sm .btn-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
}

.btn-group-sm .btn-success:hover {
    background: linear-gradient(45deg, #20c997, #17a2b8);
    transform: translateY(-1px);
}
</style>

<script>
function confirmDownload(event, materialTitle) {
    event.preventDefault();
    
    const downloadBtn = event.target.closest('.download-btn');
    const originalText = downloadBtn.innerHTML;
    
    // Show confirmation dialog
    if (confirm(`Are you sure you want to download "${materialTitle}"?\n\nThis will start the download immediately.`)) {
        // Show downloading state
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Downloading...';
        downloadBtn.classList.add('downloading');
        
        // Get the download URL and start download
        const downloadUrl = downloadBtn.href;
        
        // Create a temporary link and trigger download
        const tempLink = document.createElement('a');
        tempLink.href = downloadUrl;
        tempLink.target = '_blank';
        document.body.appendChild(tempLink);
        tempLink.click();
        document.body.removeChild(tempLink);
        
        // Show success message and reset button after a delay
        setTimeout(() => {
            downloadBtn.innerHTML = originalText;
            downloadBtn.classList.remove('downloading');
            
            // Show success toast
            showDownloadToast('Download started! Check your downloads folder.', 'success');
            
            // Refresh the page after a delay to update download counts
            setTimeout(() => {
                location.reload();
            }, 3000);
        }, 2000);
    }
}

function showDownloadToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
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
</script>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/modals.php'; ?>
<?php include 'includes/scripts.php'; ?>



<?php include 'includes/footer.php'; ?>

<?php include 'includes/modals.php'; ?>

<?php include 'includes/scripts.php'; ?>


