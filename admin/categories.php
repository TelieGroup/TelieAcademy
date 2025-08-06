<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Category.php';

$user = new User();
$category = new Category();

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? $action;
    
    if ($postAction === 'add') {
        try {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $status = $_POST['status'] ?? 'active';
            
            // Validate required fields
            if (empty($name)) {
                throw new Exception('Category name is required');
            }
            
            $result = $category->createCategory([
                'name' => $name,
                'description' => $description,
                'slug' => $slug,
                'status' => $status
            ]);
            
            if ($result['success']) {
                header('Location: categories.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error creating category: ' . $e->getMessage();
        }
    } elseif ($postAction === 'edit' && isset($_POST['category_id'])) {
        try {
            $categoryId = $_POST['category_id'];
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $status = $_POST['status'] ?? 'active';
            
            // Validate required fields
            if (empty($name)) {
                throw new Exception('Category name is required');
            }
            
            $result = $category->updateCategory($categoryId, [
                'name' => $name,
                'description' => $description,
                'slug' => $slug,
                'status' => $status
            ]);
            
            if ($result['success']) {
                header('Location: categories.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error updating category: ' . $e->getMessage();
        }
    } elseif ($postAction === 'delete' && isset($_POST['category_id'])) {
        try {
            $categoryId = $_POST['category_id'];
            
            $result = $category->deleteCategory($categoryId);
            
            if ($result['success']) {
                header('Location: categories.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error deleting category: ' . $e->getMessage();
        }
    }
}

// Set page variables for head component
$pageTitle = $action === 'add' ? 'Add New Category' : ($action === 'edit' ? 'Edit Category' : 'Manage Categories');
$pageDescription = 'Manage blog categories';

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
                <h1 class="h2">Manage Categories</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="categories.php?action=add" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>New Category
                    </a>
                </div>
            </div>

            <!-- Categories List -->
            <?php
            $allCategories = $category->getAllCategories();
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Posts Count</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allCategories)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2"></i>
                                <p>No categories found. <a href="categories.php?action=add">Create your first category</a></p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($allCategories as $cat): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                            </td>
                            <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                            <td><?php echo htmlspecialchars($cat['description'] ?? ''); ?></td>
                            <td><span class="badge bg-info"><?php echo $cat['post_count']; ?></span></td>
                            <td>
                                <?php if ($cat['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($cat['created_at'])); ?></td>
                            <td>
                                <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php elseif ($action === 'add'): ?>
            <!-- Add Category Form -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New Category</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="categories.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Categories
                    </a>
                </div>
            </div>

            <form method="POST" action="categories.php?action=add">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="form-text">This will be displayed as the category name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            <div class="form-text">A brief description of this category.</div>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" readonly>
                            <div class="form-text">The URL-friendly version of the name. Auto-generated from the category name.</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Category Information</h6>
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
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Category
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
            <!-- Edit Category Form -->
            <?php
            $categoryId = $_GET['id'];
            $categoryData = $category->getCategoryById($categoryId);
            
            if (!$categoryData) {
                echo '<div class="alert alert-danger">Category not found.</div>';
            } else {
            ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Category</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="categories.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Categories
                    </a>
                </div>
            </div>

            <form method="POST" action="categories.php?action=edit">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($categoryData['name']); ?>" required>
                            <div class="form-text">This will be displayed as the category name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($categoryData['description'] ?? ''); ?></textarea>
                            <div class="form-text">A brief description of this category.</div>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($categoryData['slug']); ?>" readonly>
                            <div class="form-text">The URL-friendly version of the name. Auto-generated from the category name.</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Category Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_active" value="active" <?php echo ($categoryData['status'] === 'active') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="status_active">Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_inactive" value="inactive" <?php echo ($categoryData['status'] === 'inactive') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="status_inactive">Inactive</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Category
                            </button>
                            <a href="categories.php" class="btn btn-outline-secondary">
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

// Delete category function
function deleteCategory(categoryId, categoryName) {
    if (confirm('Are you sure you want to delete the category "' + categoryName + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'categories.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const categoryIdInput = document.createElement('input');
        categoryIdInput.type = 'hidden';
        categoryIdInput.name = 'category_id';
        categoryIdInput.value = categoryId;
        
        form.appendChild(actionInput);
        form.appendChild(categoryIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?> 