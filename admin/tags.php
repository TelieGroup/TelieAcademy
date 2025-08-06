<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Tag.php';

$user = new User();
$tag = new Tag();

// Check if user is logged in and is admin
if (!$user->isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$currentUser = $user->getCurrentUser();
if (!$currentUser || !$currentUser['is_admin']) {
    header('Location: ../index.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? $action;
    
    if ($postAction === 'add') {
        try {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $color = $_POST['color'] ?? '#007bff';
            $status = $_POST['status'] ?? 'active';
            
            // Validate required fields
            if (empty($name)) {
                throw new Exception('Tag name is required');
            }
            
            $result = $tag->createTag([
                'name' => $name,
                'description' => $description,
                'slug' => $slug,
                'color' => $color,
                'status' => $status
            ]);
            
            if ($result['success']) {
                header('Location: tags.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error creating tag: ' . $e->getMessage();
        }
    } elseif ($postAction === 'edit' && isset($_POST['tag_id'])) {
        try {
            $tagId = $_POST['tag_id'];
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $color = $_POST['color'] ?? '#007bff';
            $status = $_POST['status'] ?? 'active';
            
            // Validate required fields
            if (empty($name)) {
                throw new Exception('Tag name is required');
            }
            
            $result = $tag->updateTag($tagId, [
                'name' => $name,
                'description' => $description,
                'slug' => $slug,
                'color' => $color,
                'status' => $status
            ]);
            
            if ($result['success']) {
                header('Location: tags.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error updating tag: ' . $e->getMessage();
        }
    } elseif ($postAction === 'delete' && isset($_POST['tag_id'])) {
        try {
            $tagId = $_POST['tag_id'];
            
            $result = $tag->deleteTag($tagId);
            
            if ($result['success']) {
                header('Location: tags.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error deleting tag: ' . $e->getMessage();
        }
    }
}

// Set page variables for head component
$pageTitle = $action === 'add' ? 'Add New Tag' : ($action === 'edit' ? 'Edit Tag' : 'Manage Tags');
$pageDescription = 'Manage blog tags';

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
            <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Tags</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="tags.php?action=add" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>New Tag
                    </a>
                </div>
            </div>

            <!-- Tags List -->
            <div class="row">
                <div class="col-md-8">
                    <?php
                    $allTags = $tag->getAllTags();
                    ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Color</th>
                                    <th>Posts Count</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allTags)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-tags fa-2x mb-2"></i>
                                        <p>No tags found. <a href="tags.php?action=add">Create your first tag</a></p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($allTags as $tagItem): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($tagItem['name']); ?></strong>
                                        <?php if ($tagItem['description']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($tagItem['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($tagItem['slug']); ?></code></td>
                                    <td>
                                        <span class="badge" style="background-color: <?php echo htmlspecialchars($tagItem['color']); ?>">
                                            <?php echo htmlspecialchars($tagItem['color']); ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo $tagItem['post_count']; ?></span></td>
                                    <td>
                                        <?php if ($tagItem['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($tagItem['created_at'])); ?></td>
                                    <td>
                                        <a href="tags.php?action=edit&id=<?php echo $tagItem['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTag(<?php echo $tagItem['id']; ?>, '<?php echo htmlspecialchars($tagItem['name']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Tag Statistics</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $stats = $tag->getTagStatistics();
                            ?>
                            <div class="mb-3">
                                <label class="form-label">Total Tags</label>
                                <h4 class="text-primary mb-0"><?php echo $stats['total_tags'] ?? 0; ?></h4>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Most Used Tag</label>
                                <p class="text-muted mb-0"><?php echo $stats['most_used'] ?? 'No tags used'; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Recently Added</label>
                                <p class="text-muted mb-0"><?php echo $stats['recently_added'] ?? 'No tags'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($action === 'add'): ?>
            <!-- Add Tag Form -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New Tag</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="tags.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Tags
                    </a>
                </div>
            </div>

            <form method="POST" action="tags.php?action=add">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tag Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="form-text">This will be displayed as the tag name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" readonly>
                            <div class="form-text">The URL-friendly version of the name. Auto-generated from the tag name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <div class="form-text">A brief description of this tag.</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Tag Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_active" value="active" checked>
                                        <label class="form-check-label" for="status_active">Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_inactive" value="inactive">
                                        <label class="form-check-label" for="status_inactive">Inactive</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <input type="color" class="form-control form-control-color" id="color" name="color" value="#007bff">
                                    <div class="form-text">Choose a color for this tag.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Tag
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
            <!-- Edit Tag Form -->
            <?php
            $tagId = $_GET['id'];
            $tagData = $tag->getTagById($tagId);
            
            if (!$tagData) {
                echo '<div class="alert alert-danger">Tag not found.</div>';
            } else {
            ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Tag</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="tags.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Tags
                    </a>
                </div>
            </div>

            <form method="POST" action="tags.php?action=edit">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="tag_id" value="<?php echo $tagId; ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tag Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($tagData['name']); ?>" required>
                            <div class="form-text">This will be displayed as the tag name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($tagData['slug']); ?>" readonly>
                            <div class="form-text">The URL-friendly version of the name. Auto-generated from the tag name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($tagData['description'] ?? ''); ?></textarea>
                            <div class="form-text">A brief description of this tag.</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Tag Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_active" value="active" <?php echo ($tagData['status'] === 'active') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="status_active">Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_inactive" value="inactive" <?php echo ($tagData['status'] === 'inactive') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="status_inactive">Inactive</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <input type="color" class="form-control form-control-color" id="color" name="color" value="<?php echo htmlspecialchars($tagData['color'] ?? '#007bff'); ?>">
                                    <div class="form-text">Choose a color for this tag.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Tag
                            </button>
                            <a href="tags.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            <?php } ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slug').value = slug;
});

// Delete tag function
function deleteTag(tagId, tagName) {
    if (confirm('Are you sure you want to delete the tag "' + tagName + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'tags.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const tagIdInput = document.createElement('input');
        tagIdInput.type = 'hidden';
        tagIdInput.name = 'tag_id';
        tagIdInput.value = tagId;
        
        form.appendChild(actionInput);
        form.appendChild(tagIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?> 