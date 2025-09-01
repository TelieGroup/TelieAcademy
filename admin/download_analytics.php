<?php
require_once '../config/session.php';
require_once '../includes/Course.php';
require_once '../includes/User.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

$course = new Course();
$user = new User();

// Get download statistics
$downloadStats = $course->getDownloadStatistics();
$popularMaterials = $course->getPopularMaterials(20);

// Get recent downloads (last 30 days)
$recentDownloads = $course->getRecentDownloads(30);

include '../includes/head.php';
?>

<style>
.analytics-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.analytics-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.download-chart {
    height: 300px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.material-row {
    transition: all 0.2s ease;
}

.material-row:hover {
    background-color: #f8f9fa;
}

.download-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.preview-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background-color: #17a2b8;
    color: white;
}

/* Dark Mode Support */
.dark-mode .container-fluid {
    background: #1a1a1a;
    color: #e0e0e0;
}

.dark-mode .analytics-card {
    background: #2d2d2d;
    border-color: #404040;
    color: #e0e0e0;
}

.dark-mode .analytics-card:hover {
    background: #353535;
    border-color: #505050;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.dark-mode .stat-number {
    color: #007bff;
}

.dark-mode .stat-label {
    color: #b0b0b0;
}

.dark-mode .download-chart {
    background: linear-gradient(135deg, #2d2d2d 0%, #404040 100%);
    color: #b0b0b0;
}

.dark-mode .material-row:hover {
    background-color: #353535;
}

.dark-mode .card-header {
    background: #353535;
    border-bottom-color: #404040;
    color: #e0e0e0;
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

.dark-mode .breadcrumb {
    background: #2d2d2d;
    border-color: #404040;
}

.dark-mode .breadcrumb-item a {
    color: #007bff;
}

.dark-mode .breadcrumb-item.active {
    color: #b0b0b0;
}

.dark-mode .text-muted {
    color: #b0b0b0 !important;
}

.dark-mode .btn-outline-secondary {
    background: #404040;
    border-color: #505050;
    color: #e0e0e0;
}

.dark-mode .btn-outline-secondary:hover {
    background: #505050;
    border-color: #606060;
    color: #ffffff;
}
</style>

<?php include '../includes/header.php'; ?>

<!-- Admin CSS -->
<link rel="stylesheet" href="admin.css">

<div class="container-fluid pt-5">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Download Analytics</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-line text-primary"></i>
                        Material Analytics
                        <?php
                        try {
                            $downloadsToday = $downloadStats['downloads_today'] ?? 0;
                            $downloadsWeek = $downloadStats['downloads_week'] ?? 0;
                            $totalPreviews = $downloadStats['total_previews'] ?? 0;
                            $previewsToday = $downloadStats['previews_today'] ?? 0;
                            if ($downloadsToday > 0):
                            ?>
                            <span class="badge bg-success ms-2"><?php echo $downloadsToday; ?> Downloads Today</span>
                            <?php endif; ?>
                            <?php if ($previewsToday > 0): ?>
                            <span class="badge bg-info ms-2"><?php echo $previewsToday; ?> Previews Today</span>
                            <?php endif; ?>
                            <?php if ($totalPreviews > 0): ?>
                            <span class="badge bg-warning text-dark ms-2"><?php echo number_format($totalPreviews); ?> Total Previews</span>
                            <?php endif; ?>
                        <?php } catch (Exception $e) { /* Silently fail */ } ?>
                    </h1>
                    <p class="text-muted mb-0">Track and analyze course material downloads and previews</p>
                </div>
                <div>
                    <a href="index" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo number_format($downloadStats['total_downloads']); ?></div>
                            <div class="stat-label">Total Downloads</div>
                            <i class="fas fa-download fa-2x text-primary mt-2"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo number_format($downloadStats['total_previews']); ?></div>
                            <div class="stat-label">Total Previews</div>
                            <i class="fas fa-eye fa-2x text-info mt-2"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo number_format($downloadStats['downloads_today']); ?></div>
                            <div class="stat-label">Downloads Today</div>
                            <i class="fas fa-calendar-day fa-2x text-success mt-2"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo number_format($downloadStats['previews_today']); ?></div>
                            <div class="stat-label">Previews Today</div>
                            <i class="fas fa-eye fa-2x text-info mt-2"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo number_format($downloadStats['downloads_week']); ?></div>
                            <div class="stat-label">Downloads This Week</div>
                            <i class="fas fa-calendar-week fa-2x text-warning mt-2"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo number_format($downloadStats['previews_week']); ?></div>
                            <div class="stat-label">Previews This Week</div>
                            <i class="fas fa-calendar-week fa-2x text-info mt-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Statistics Row -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo count($popularMaterials); ?></div>
                            <div class="stat-label">Active Materials</div>
                            <i class="fas fa-file-alt fa-2x text-secondary mt-2"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <?php 
                            $totalEngagement = ($downloadStats['total_downloads'] ?? 0) + ($downloadStats['total_previews'] ?? 0);
                            $engagementRate = count($popularMaterials) > 0 ? round($totalEngagement / count($popularMaterials), 1) : 0;
                            ?>
                            <div class="stat-number"><?php echo number_format($engagementRate); ?></div>
                            <div class="stat-label">Avg Engagement per Material</div>
                            <i class="fas fa-chart-line fa-2x text-success mt-2"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <?php 
                            $previewToDownloadRatio = ($downloadStats['total_downloads'] ?? 0) > 0 ? 
                                round((($downloadStats['total_previews'] ?? 0) / ($downloadStats['total_downloads'] ?? 1)) * 100, 1) : 0;
                            ?>
                            <div class="stat-number"><?php echo $previewToDownloadRatio; ?>%</div>
                            <div class="stat-label">Preview to Download Ratio</div>
                            <i class="fas fa-percentage fa-2x text-warning mt-2"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card analytics-card">
                        <div class="card-body text-center">
                            <?php 
                            $totalEngagement = ($downloadStats['total_downloads'] ?? 0) + ($downloadStats['total_previews'] ?? 0);
                            ?>
                            <div class="stat-number"><?php echo number_format($totalEngagement); ?></div>
                            <div class="stat-label">Total Engagement</div>
                            <i class="fas fa-users fa-2x text-primary mt-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Materials Chart -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Material Engagement Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="download-chart">
                                <div class="text-center">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="text-primary">
                                                    <i class="fas fa-download me-2"></i>
                                                    Downloads: <?php echo number_format($downloadStats['total_downloads']); ?>
                                                </h6>
                                                <div class="progress" style="height: 20px;">
                                                    <?php 
                                                    $totalEngagement = ($downloadStats['total_downloads'] ?? 0) + ($downloadStats['total_previews'] ?? 0);
                                                    $downloadPercentage = $totalEngagement > 0 ? round((($downloadStats['total_downloads'] ?? 0) / $totalEngagement) * 100) : 0;
                                                    ?>
                                                    <div class="progress-bar bg-primary" style="width: <?php echo $downloadPercentage; ?>%">
                                                        <?php echo $downloadPercentage; ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="text-info">
                                                    <i class="fas fa-eye me-2"></i>
                                                    Previews: <?php echo number_format($downloadStats['total_previews']); ?>
                                                </h6>
                                                <div class="progress" style="height: 20px;">
                                                    <?php 
                                                    $previewPercentage = $totalEngagement > 0 ? round((($downloadStats['total_previews'] ?? 0) / $totalEngagement) * 100) : 0;
                                                    ?>
                                                    <div class="progress-bar bg-info" style="width: <?php echo $previewPercentage; ?>%">
                                                        <?php echo $previewPercentage; ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            Total Engagement: <?php echo number_format($totalEngagement); ?> interactions across <?php echo count($popularMaterials); ?> materials
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Materials Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card analytics-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-star me-2"></i>
                                Material Performance Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Material</th>
                                            <th>Module</th>
                                            <th>Course</th>
                                            <th>Downloads</th>
                                            <th>Previews</th>
                                            <th>File Type</th>
                                            <th>Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($popularMaterials)): ?>
                                            <?php foreach ($popularMaterials as $index => $material): ?>
                                                <tr class="material-row">
                                                    <td>
                                                        <span class="badge bg-primary">#<?php echo $index + 1; ?></span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($material['title']); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($material['description']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($material['module_title']); ?></td>
                                                    <td><?php echo htmlspecialchars($material['course_title']); ?></td>
                                                    <td>
                                                        <span class="badge bg-success download-badge">
                                                            <i class="fas fa-download me-1"></i>
                                                            <?php echo number_format($material['download_count'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info preview-badge">
                                                            <i class="fas fa-eye me-1"></i>
                                                            <?php echo number_format($material['preview_count'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo strtoupper($material['file_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('M j, Y', strtotime($material['updated_at'] ?? $material['created_at'])); ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>No materials found</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </main>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
