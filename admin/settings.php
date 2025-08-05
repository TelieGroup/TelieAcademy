<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Settings.php';

$user = new User();
$settings = new Settings();

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $settingsData = $_POST['settings'] ?? [];
        $processedSettings = [];
        foreach ($settingsData as $key => $value) {
            $processedSettings[$key] = trim($value);
        }
        
        if ($settings->updateSettings($processedSettings)) {
            $success = 'Settings updated successfully!';
        } else {
            $error = 'Failed to update settings.';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$allSettings = $settings->getAllSettings();
$pageTitle = 'Settings';
$pageDescription = 'Manage blog settings';

include '../includes/head.php';
?>
<link rel="stylesheet" href="admin.css">
<?php include '../includes/header.php'; ?>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="posts.php"><i class="fas fa-file-alt me-2"></i>Posts</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php"><i class="fas fa-folder me-2"></i>Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="tags.php"><i class="fas fa-tags me-2"></i>Tags</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users me-2"></i>Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="comments.php"><i class="fas fa-comments me-2"></i>Comments</a></li>
                    <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Settings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="submit" form="settingsForm" class="btn btn-sm btn-primary">
                        <i class="fas fa-save me-1"></i>Save Settings
                    </button>
                </div>
            </div>

            <form id="settingsForm" method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <!-- General Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">General Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="admin_email" class="form-label">Admin Email</label>
                                            <input type="email" class="form-control" id="admin_email" name="settings[admin_email]" value="<?php echo htmlspecialchars($allSettings['admin_email'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contact_email" class="form-label">Contact Email</label>
                                            <input type="email" class="form-control" id="contact_email" name="settings[contact_email]" value="<?php echo htmlspecialchars($allSettings['contact_email'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Site Description</label>
                                    <textarea class="form-control" id="site_description" name="settings[site_description]" rows="3"><?php echo htmlspecialchars($allSettings['site_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Content Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Content Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="posts_per_page" class="form-label">Posts Per Page</label>
                                            <select class="form-select" id="posts_per_page" name="settings[posts_per_page]">
                                                <option value="5" <?php echo ($allSettings['posts_per_page'] ?? '10') == '5' ? 'selected' : ''; ?>>5</option>
                                                <option value="10" <?php echo ($allSettings['posts_per_page'] ?? '10') == '10' ? 'selected' : ''; ?>>10</option>
                                                <option value="15" <?php echo ($allSettings['posts_per_page'] ?? '10') == '15' ? 'selected' : ''; ?>>15</option>
                                                <option value="20" <?php echo ($allSettings['posts_per_page'] ?? '10') == '20' ? 'selected' : ''; ?>>20</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_text" class="form-label">Footer Text</label>
                                            <input type="text" class="form-control" id="footer_text" name="settings[footer_text]" value="<?php echo htmlspecialchars($allSettings['footer_text'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="comments_enabled" name="settings[comments_enabled]" value="1" <?php echo ($allSettings['comments_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="comments_enabled">Enable Comments</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="newsletter_enabled" name="settings[newsletter_enabled]" value="1" <?php echo ($allSettings['newsletter_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="newsletter_enabled">Enable Newsletter</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="premium_content_enabled" name="settings[premium_content_enabled]" value="1" <?php echo ($allSettings['premium_content_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="premium_content_enabled">Premium Content</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Settings Info</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Total Settings</label>
                                    <h4 class="text-primary mb-0"><?php echo count($allSettings); ?></h4>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" form="settingsForm" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
// Handle checkbox values
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (!checkbox.checked) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = checkbox.name;
            hiddenInput.value = '0';
            checkbox.parentNode.appendChild(hiddenInput);
        }
        
        checkbox.addEventListener('change', function() {
            const hiddenInput = this.parentNode.querySelector('input[type="hidden"][name="' + this.name + '"]');
            if (this.checked) {
                if (hiddenInput) hiddenInput.remove();
            } else {
                if (!hiddenInput) {
                    const newHiddenInput = document.createElement('input');
                    newHiddenInput.type = 'hidden';
                    newHiddenInput.name = this.name;
                    newHiddenInput.value = '0';
                    this.parentNode.appendChild(newHiddenInput);
                }
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?> 