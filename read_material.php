<?php
require_once 'config/session.php';
require_once 'includes/Course.php';
require_once 'includes/User.php';

$course = new Course();
$user = new User();

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

// Get material ID from URL
$materialId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$materialId) {
    header('Location: courses.php');
    exit;
}

// Get material details
$material = $course->getMaterialById($materialId);
if (!$material || !$material['is_active']) {
    header('Location: courses.php');
    exit;
}

// Get course details for breadcrumb
$courseData = $course->getCourseById($material['course_id']);
$moduleData = $course->getModuleById($material['module_id']);

// Set page variables for head component
$pageTitle = $material['title'] . " - Telie Academy";
$pageDescription = $material['description'];

include 'includes/head.php';
?>

<style>
/* Material Reader Styling */
.material-reader {
    background: #f8f9fa;
    min-height: 100vh;
}

.reader-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.reader-header h1 {
    color: white;
    margin-bottom: 0.5rem;
}

.reader-header .breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.reader-header .breadcrumb-item a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
}

.reader-header .breadcrumb-item.active {
    color: white;
}

.reader-header .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255,255,255,0.6);
}

.material-info {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.material-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.material-actions .btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.material-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.reader-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    min-height: 600px;
}

.reader-container iframe {
    width: 100%;
    height: 80vh;
    border: none;
}

.reader-container .pdf-viewer {
    width: 100%;
    height: 80vh;
}

.reader-container .text-content {
    padding: 2rem;
    line-height: 1.8;
    font-size: 1.1rem;
}

.reader-container .image-viewer {
    text-align: center;
    padding: 2rem;
}

.reader-container .image-viewer img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.reader-container .unsupported-format {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.reader-container .unsupported-format i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .reader-header {
        padding: 1.5rem 0;
    }
    
    .material-actions {
        flex-direction: column;
    }
    
    .material-actions .btn {
        width: 100%;
    }
    
    .reader-container iframe,
    .reader-container .pdf-viewer {
        height: 60vh;
    }
}
</style>

<?php include 'includes/header.php'; ?>

<!-- Reading Progress Bar -->
<div class="reading-progress-bar" id="readingProgressBar"></div>

<div class="material-reader">
    <!-- Reader Header -->
    <div class="reader-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="courses.php">Course Materials</a></li>
                            <?php if ($courseData): ?>
                                <li class="breadcrumb-item"><a href="course.php?slug=<?php echo $courseData['slug']; ?>"><?php echo htmlspecialchars($courseData['title']); ?></a></li>
                            <?php endif; ?>
                            <?php if ($moduleData): ?>
                                <li class="breadcrumb-item"><?php echo htmlspecialchars($moduleData['title']); ?></li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($material['title']); ?></li>
                        </ol>
                    </nav>
                    
                    <h1 class="display-5"><?php echo htmlspecialchars($material['title']); ?></h1>
                    <p class="lead mb-0"><?php echo htmlspecialchars($material['description']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Material Information -->
                <div class="material-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-file-<?php echo strtolower($material['file_type']) === 'pdf' ? 'pdf' : 'word'; ?> text-primary me-2" style="font-size: 1.5rem;"></i>
                                <span class="badge bg-secondary me-2"><?php echo strtoupper($material['file_type']); ?></span>
                                <small class="text-muted"><?php echo number_format($material['file_size'] / 1024, 1); ?> KB</small>
                            </div>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-1"></i>
                                Added on <?php echo date('F j, Y', strtotime($material['created_at'])); ?>
                                <?php if ($material['download_count'] > 0): ?>
                                    <span class="ms-3">
                                        <i class="fas fa-download me-1"></i>
                                        <?php echo $material['download_count']; ?> download<?php echo $material['download_count'] !== 1 ? 's' : ''; ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="material-actions">
                                <a href="download_material.php?id=<?php echo $material['id']; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-download me-2"></i>Download
                                </a>
                                <a href="courses.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Courses
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material Reader Container -->
                <div class="reader-container">
                    <?php
                    $filePath = str_replace('../', '', $material['file_path']);
                    $fileType = strtolower($material['file_type']);
                    $fileExtension = strtolower(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                    
                    // Determine how to display the material based on file type
                    if ($fileType === 'pdf' || $fileExtension === 'pdf'): ?>
                        <!-- PDF Viewer -->
                        <iframe src="<?php echo htmlspecialchars($filePath); ?>" 
                                class="pdf-viewer" 
                                title="<?php echo htmlspecialchars($material['title']); ?>">
                            <p>Your browser doesn't support PDF viewing. 
                               <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank">Click here to download the PDF</a>.</p>
                        </iframe>
                    <?php elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                        <!-- Image Viewer -->
                        <div class="image-viewer">
                            <img src="<?php echo htmlspecialchars($filePath); ?>" 
                                 alt="<?php echo htmlspecialchars($material['title']); ?>"
                                 class="img-fluid">
                        </div>
                    <?php elseif (in_array($fileExtension, ['txt', 'md'])): ?>
                        <!-- Text Viewer -->
                        <div class="text-content">
                            <?php
                            $textContent = file_get_contents($filePath);
                            if ($textContent !== false) {
                                echo nl2br(htmlspecialchars($textContent));
                            } else {
                                echo '<p class="text-muted">Unable to read text content. Please download the file instead.</p>';
                            }
                            ?>
                        </div>
                    <?php elseif (in_array($fileExtension, ['html', 'htm'])): ?>
                        <!-- HTML Viewer -->
                        <iframe src="<?php echo htmlspecialchars($filePath); ?>" 
                                class="pdf-viewer" 
                                title="<?php echo htmlspecialchars($material['title']); ?>">
                            <p>Your browser doesn't support HTML viewing. 
                               <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank">Click here to open the HTML file</a>.</p>
                        </iframe>
                    <?php else: ?>
                        <!-- Unsupported Format -->
                        <div class="unsupported-format">
                            <i class="fas fa-file-alt"></i>
                            <h3>Preview Not Available</h3>
                            <p class="mb-3">This file type cannot be previewed in the browser.</p>
                            <a href="download_material.php?id=<?php echo $material['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-download me-2"></i>Download to View
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Track reading time for analytics
let startTime = Date.now();
let hasInteracted = false;

// Track user interaction
document.addEventListener('click', function() {
    if (!hasInteracted) {
        hasInteracted = true;
    }
});

document.addEventListener('scroll', function() {
    if (!hasInteracted) {
        hasInteracted = true;
    }
});

// Track when user leaves the page
window.addEventListener('beforeunload', function() {
    if (hasInteracted) {
        const readingTime = Math.round((Date.now() - startTime) / 1000);
        // You can send this data to your analytics system
        console.log('Reading time:', readingTime, 'seconds');
    }
});

// Add keyboard shortcuts for navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Go back to courses
        window.location.href = 'courses.php';
    }
});
</script>

<?php include 'includes/footer.php'; ?>

