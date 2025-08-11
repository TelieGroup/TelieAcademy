<?php
// Include necessary files
require_once 'config/session.php';
require_once 'includes/User.php';

// Initialize user object
$user = new User();

// Check if user is logged in
$isLoggedIn = $user->isLoggedIn();
$currentUser = null;

if ($isLoggedIn) {
    $currentUser = $user->getCurrentUser();
}

// Handle logout if requested
$logoutMessage = '';
$logoutType = '';

if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
    // Perform logout
    $result = $user->logout();
    
    if ($result['success']) {
        $logoutMessage = $result['message'];
        $logoutType = 'success';
        $isLoggedIn = false;
        $currentUser = null;
    } else {
        $logoutMessage = $result['message'];
        $logoutType = 'error';
    }
}

// Set page title
$pageTitle = 'Logout - TelieAcademy';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content -->
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($logoutMessage)): ?>
                            <div class="alert alert-<?php echo $logoutType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                <i class="fas fa-<?php echo $logoutType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                <?php echo htmlspecialchars($logoutMessage); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn && $currentUser): ?>
                            <div class="text-center mb-4">
                                <div class="mb-3">
                                    <?php if (!empty($currentUser['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($currentUser['profile_picture']); ?>" 
                                             alt="Profile Picture" 
                                             class="rounded-circle" 
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                                    <?php endif; ?>
                                </div>
                                <h5>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?>!</h5>
                                <p class="text-muted">Are you sure you want to logout?</p>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="logout.php?confirm=true" class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Yes, Logout
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Cancel
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-user-slash fa-4x text-muted mb-3"></i>
                                <h5>You are not logged in</h5>
                                <p class="text-muted">There's nothing to logout from.</p>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>
                                    Go to Homepage
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($logoutType === 'success'): ?>
                    <div class="text-center mt-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Redirecting to homepage...</p>
                    </div>
                    <script>
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 2000);
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="script.js"></script>
</body>
</html> 