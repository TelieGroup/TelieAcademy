<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Post.php';
require_once '../includes/Category.php';
require_once '../includes/Tag.php'; // Added Tag.php

$user = new User();
$post = new Post();
$category = new Category();
$tag = new Tag(); // Added Tag object

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
    // Debug: Log the action
    error_log("Admin POST action: " . $action);
    error_log("POST data: " . print_r($_POST, true));
    
    // Check if action is set in POST data
    $postAction = $_POST['action'] ?? $action;
    error_log("Processing action: $postAction");
    
    if ($postAction === 'add') {
        try {
            $title = $_POST['title'] ?? '';
            $excerpt = $_POST['excerpt'] ?? '';
            $content = $_POST['content'] ?? '';
            $categoryId = $_POST['category_id'] ?? '';
            $status = $_POST['status'] ?? 'draft';
            $isPremium = isset($_POST['is_premium']) ? 1 : 0;
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            $tags = $_POST['tags'] ?? '';
            
            // Debug: Log the data
            error_log("Post creation data - Title: $title, Category: $categoryId, Author: " . $currentUser['id']);
            error_log("Content length: " . strlen($content));
            
            // Validate required fields
            if (empty($title)) {
                throw new Exception('Title is required');
            }
            if (empty($content)) {
                throw new Exception('Content is required');
            }
            if (empty($categoryId)) {
                throw new Exception('Category is required');
            }
            
            $result = $post->createPost([
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'category_id' => $categoryId,
                'status' => $status,
                'is_premium' => $isPremium,
                'is_featured' => $isFeatured,
                'author_id' => $currentUser['id'],
                'tags' => $tags
            ]);
            
            // Debug: Log the result
            error_log("Post creation result: " . print_r($result, true));
            
            if ($result['success']) {
                header('Location: posts.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error creating post: ' . $e->getMessage();
            error_log("Post creation error: " . $e->getMessage());
        }
    } elseif ($postAction === 'delete' && isset($_POST['post_id'])) {
        try {
            $postId = $_POST['post_id'];
            error_log("Deleting post ID: $postId");
            
            $result = $post->deletePost($postId);
            
            error_log("Delete result: " . print_r($result, true));
            
            if ($result['success']) {
                header('Location: posts.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error deleting post: ' . $e->getMessage();
            error_log("Delete error: " . $e->getMessage());
        }
    } elseif ($postAction === 'edit' && isset($_POST['post_id'])) {
        try {
            $postId = $_POST['post_id'];
            $title = $_POST['title'] ?? '';
            $excerpt = $_POST['excerpt'] ?? '';
            $content = $_POST['content'] ?? '';
            $categoryId = $_POST['category_id'] ?? '';
            $status = $_POST['status'] ?? 'draft';
            $isPremium = isset($_POST['is_premium']) ? 1 : 0;
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            $tags = $_POST['tags'] ?? '';
            
            // Debug: Log the data
            error_log("Post update data - ID: $postId, Title: $title, Category: $categoryId, Tags: $tags");
            
            // Validate required fields
            if (empty($title)) {
                throw new Exception('Title is required');
            }
            if (empty($content)) {
                throw new Exception('Content is required');
            }
            if (empty($categoryId)) {
                throw new Exception('Category is required');
            }
            
            $result = $post->updatePost($postId, [
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'category_id' => $categoryId,
                'status' => $status,
                'is_premium' => $isPremium,
                'is_featured' => $isFeatured,
                'tags' => $tags
            ]);
            
            // Debug: Log the result
            error_log("Post update result: " . print_r($result, true));
            
            if ($result['success']) {
                header('Location: posts.php?message=' . urlencode($result['message']));
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error updating post: ' . $e->getMessage();
            error_log("Post update error: " . $e->getMessage());
        }
    }
}

// Set page variables for head component
$pageTitle = $action === 'add' ? 'Add New Post' : ($action === 'edit' ? 'Edit Post' : 'Manage Posts');
$pageDescription = 'Manage your blog posts';

include '../includes/head.php';
?>
<!-- Admin CSS -->
<link rel="stylesheet" href="admin.css">
<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material.min.css">
<style>
/* Ensure CodeMirror textarea is accessible for form validation */
.CodeMirror {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    font-family: 'Courier New', Courier, monospace;
    font-size: 14px;
    height: 400px;
}

/* Hide the original textarea but keep it accessible */
#content {
    position: absolute;
    left: -9999px;
    width: 1px;
    height: 1px;
    opacity: 0;
}
</style>
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

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Posts</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="posts.php?action=add" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>New Post
                    </a>
                </div>
            </div>

            <?php if ($action === 'list'): ?>
            <!-- Posts List -->
            <?php
            // Get all posts from database (including drafts)
            $allPosts = $post->getAllPostsForAdmin();
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allPosts)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No posts found. <a href="posts.php?action=add">Create your first post</a></p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($allPosts as $postItem): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($postItem['title']); ?></strong>
                                <?php if ($postItem['is_premium']): ?>
                                    <span class="badge bg-warning ms-1">Premium</span>
                                <?php endif; ?>
                                <?php if ($postItem['is_featured']): ?>
                                    <span class="badge bg-info ms-1">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($postItem['category_name'] ?? 'Uncategorized'); ?></td>
                            <td>
                                <?php if ($postItem['status'] === 'published'): ?>
                                    <span class="badge bg-success">Published</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo number_format($postItem['view_count'] ?? 0); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($postItem['created_at'])); ?></td>
                            <td>
                                <a href="posts.php?action=edit&id=<?php echo $postItem['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deletePost(<?php echo $postItem['id']; ?>)">
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
            <!-- Add Post Form -->
            <form method="POST" action="posts.php?action=add">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <div class="rich-text-editor">
                                <!-- Editor Toolbar -->
                                <div class="editor-toolbar mb-2">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('bold')" title="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('italic')" title="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('underline')" title="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('strikethrough')" title="Strikethrough">
                                            <i class="fas fa-strikethrough"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('heading1')" title="Heading 1">
                                            <i class="fas fa-heading"></i>1
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('heading2')" title="Heading 2">
                                            <i class="fas fa-heading"></i>2
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('heading3')" title="Heading 3">
                                            <i class="fas fa-heading"></i>3
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('unorderedList')" title="Unordered List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('orderedList')" title="Ordered List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('blockquote')" title="Blockquote">
                                            <i class="fas fa-quote-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFormat()" title="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCodeBlock()" title="Code Block">
                                            <i class="fas fa-code"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertInlineCode()" title="Inline Code">
                                            <i class="fas fa-code"></i> Inline
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink()" title="Insert Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertImage()" title="Insert Image">
                                            <i class="fas fa-image"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTable()" title="Insert Table">
                                            <i class="fas fa-table"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertHorizontalRule()" title="Horizontal Rule">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePreview()" title="Toggle Preview">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <select class="form-select form-select-sm" id="codeTheme" onchange="changeCodeTheme()">
                                            <option value="default">Default Theme</option>
                                            <option value="monokai">Monokai</option>
                                            <option value="dracula">Dracula</option>
                                            <option value="material">Material</option>
                                        </select>
                                        <select class="form-select form-select-sm" id="codeLanguage" onchange="changeCodeLanguage()">
                                            <option value="javascript">JavaScript</option>
                                            <option value="python">Python</option>
                                            <option value="php">PHP</option>
                                            <option value="html">HTML</option>
                                            <option value="css">CSS</option>
                                            <option value="sql">SQL</option>
                                            <option value="bash">Bash</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Editor Content Area -->
                                <div class="editor-container">
                                    <div id="editor" class="editor-content" contenteditable="true" style="min-height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; background-color: white;">
                                        <p>Start writing your content here...</p>
                                    </div>
                                    
                                    <!-- Hidden textarea for form submission -->
                                    <textarea class="form-control" id="content" name="content" rows="20" style="display: none;" required></textarea>
                                </div>
                                
                                <!-- Preview Area -->
                                <div id="previewArea" class="preview-content mt-3" style="display: none; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; background-color: #f8f9fa; min-height: 200px;">
                                    <h6 class="text-muted mb-2">Preview</h6>
                                    <div id="previewContent"></div>
                                </div>
                                
                                <div class="form-text">
                                    Use the toolbar buttons to format your content. You can also use keyboard shortcuts like <kbd>Ctrl+B</kbd> for bold, <kbd>Ctrl+I</kbd> for italic, <kbd>Ctrl+U</kbd> for underline, <kbd>Ctrl+K</kbd> for link, and <kbd>Ctrl+Shift+X</kbd> to clear formatting.
                                </div>
                            </div>
                        </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php
                                // Get all categories from the database
                                $allCategories = $category->getAllCategories();
                                foreach ($allCategories as $cat):
                                ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter tags separated by commas">
                            <div class="form-text">Enter tags separated by commas (e.g., javascript, react, web-development)</div>
                            <div class="mt-2">
                                <small class="text-muted">Available tags:</small>
                                <div class="mt-1">
                                    <?php
                                    $allTags = $tag->getAllTagsWithProperties();
                                    foreach ($allTags as $tagItem):
                                    ?>
                                    <span class="badge me-1 mb-1" style="background-color: <?php echo htmlspecialchars($tagItem['color']); ?>; color: white; cursor: pointer;" onclick="addTagToInput('<?php echo htmlspecialchars($tagItem['name']); ?>')">
                                        <?php echo htmlspecialchars($tagItem['name']); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium">
                                <label class="form-check-label" for="is_premium">Premium Content</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                <label class="form-check-label" for="is_featured">Featured Post</label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Post
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
            <!-- Edit Post Form -->
            <?php
            $postId = $_GET['id'];
            $postData = $post->getPostById($postId);
            
            if (!$postData) {
                echo '<div class="alert alert-danger">Post not found.</div>';
            } else {
            ?>
            <form method="POST" action="posts.php?action=edit">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($postData['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($postData['excerpt']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <div class="rich-text-editor">
                                <!-- Editor Toolbar -->
                                <div class="editor-toolbar mb-2">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('bold')" title="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('italic')" title="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('underline')" title="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('strikethrough')" title="Strikethrough">
                                            <i class="fas fa-strikethrough"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('heading1')" title="Heading 1">
                                            <i class="fas fa-heading"></i>1
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('heading2')" title="Heading 2">
                                            <i class="fas fa-heading"></i>2
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('heading3')" title="Heading 3">
                                            <i class="fas fa-heading"></i>3
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('unorderedList')" title="Unordered List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('orderedList')" title="Ordered List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('blockquote')" title="Blockquote">
                                            <i class="fas fa-quote-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFormat()" title="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCodeBlock()" title="Code Block">
                                            <i class="fas fa-code"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertInlineCode()" title="Inline Code">
                                            <i class="fas fa-code"></i> Inline
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink()" title="Insert Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertImage()" title="Insert Image">
                                            <i class="fas fa-image"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTable()" title="Insert Table">
                                            <i class="fas fa-table"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertHorizontalRule()" title="Horizontal Rule">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePreview()" title="Toggle Preview">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ms-2" role="group">
                                        <select class="form-select form-select-sm" id="codeTheme" onchange="changeCodeTheme()">
                                            <option value="default">Default Theme</option>
                                            <option value="monokai">Monokai</option>
                                            <option value="dracula">Dracula</option>
                                            <option value="material">Material</option>
                                        </select>
                                        <select class="form-select form-select-sm" id="codeLanguage" onchange="changeCodeLanguage()">
                                            <option value="javascript">JavaScript</option>
                                            <option value="python">Python</option>
                                            <option value="php">PHP</option>
                                            <option value="html">HTML</option>
                                            <option value="css">CSS</option>
                                            <option value="sql">SQL</option>
                                            <option value="bash">Bash</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Editor Content Area -->
                                <div class="editor-container">
                                    <div id="editor" class="editor-content" contenteditable="true" style="min-height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; background-color: white;">
                                        <?php echo $postData['content'] ?? '<p>Start writing your content here...</p>'; ?>
                                    </div>
                                    
                                    <!-- Hidden textarea for form submission -->
                                    <textarea class="form-control" id="content" name="content" rows="20" style="display: none;" required><?php echo htmlspecialchars($postData['content'] ?? ''); ?></textarea>
                                </div>
                                
                                <!-- Preview Area -->
                                <div id="previewArea" class="preview-content mt-3" style="display: none; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; background-color: #f8f9fa; min-height: 200px;">
                                    <h6 class="text-muted mb-2">Preview</h6>
                                    <div id="previewContent"></div>
                                </div>
                                
                                <div class="form-text">
                                    Use the toolbar buttons to format your content. You can also use keyboard shortcuts like <kbd>Ctrl+B</kbd> for bold, <kbd>Ctrl+I</kbd> for italic, <kbd>Ctrl+U</kbd> for underline, <kbd>Ctrl+K</kbd> for link, and <kbd>Ctrl+Shift+X</kbd> to clear formatting.
                                </div>
                            </div>
                        </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php
                                // Get all categories from the database
                                $allCategories = $category->getAllCategories();
                                foreach ($allCategories as $cat):
                                ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $postData['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags" value="<?php echo htmlspecialchars(is_array($postData['tags']) ? implode(', ', $postData['tags']) : $postData['tags']); ?>" placeholder="Enter tags separated by commas">
                            <div class="form-text">Enter tags separated by commas (e.g., javascript, react, web-development)</div>
                            <div class="mt-2">
                                <small class="text-muted">Available tags:</small>
                                <div class="mt-1">
                                    <?php
                                    $allTags = $tag->getAllTagsWithProperties();
                                    foreach ($allTags as $tagItem):
                                    ?>
                                    <span class="badge me-1 mb-1" style="background-color: <?php echo htmlspecialchars($tagItem['color']); ?>; color: white; cursor: pointer;" onclick="addTagToInput('<?php echo htmlspecialchars($tagItem['name']); ?>')">
                                        <?php echo htmlspecialchars($tagItem['name']); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?php echo ($postData['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($postData['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" <?php echo ($postData['is_premium']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_premium">Premium Content</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" <?php echo ($postData['is_featured']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">Featured Post</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Post
                            </button>
                            <a href="posts.php" class="btn btn-outline-secondary">
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

<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/python/python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
// Rich Text Editor Variables
let editor;
let currentCodeLanguage = 'javascript';
let currentCodeTheme = 'default';

// Initialize the rich text editor
document.addEventListener('DOMContentLoaded', function() {
    initializeRichTextEditor();
});

function initializeRichTextEditor() {
    editor = document.getElementById('editor');
    const contentTextarea = document.getElementById('content');
    
    if (!editor) return;
    
    // Set initial content if editing existing post
    if (contentTextarea && contentTextarea.value.trim()) {
        // Decode HTML entities for display in editor
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = contentTextarea.value;
        editor.innerHTML = tempDiv.innerHTML;
    } else if (editor.innerHTML.trim() === '<p>Start writing your content here...</p>') {
        // Clear the default content if user starts typing
        editor.addEventListener('focus', function(e) {
            if (editor.innerHTML.trim() === '<p>Start writing your content here...</p>') {
                editor.innerHTML = '';
            }
        }, { once: true });
    }
    
    // Sync content with hidden textarea on input
    editor.addEventListener('input', function() {
        if (contentTextarea) {
            contentTextarea.value = editor.innerHTML;
        }
    });
    
    // Add keyboard shortcuts
    editor.addEventListener('keydown', function(e) {
        handleKeyboardShortcuts(e);
    });
    
    // Focus the editor
    editor.focus();
}

// Handle keyboard shortcuts
function handleKeyboardShortcuts(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key.toLowerCase()) {
            case 'b':
                e.preventDefault();
                formatText('bold');
                break;
            case 'i':
                e.preventDefault();
                formatText('italic');
                break;
            case 'u':
                e.preventDefault();
                formatText('underline');
                break;
            case 'k':
                e.preventDefault();
                insertLink();
                break;
            case 'x':
                if (e.shiftKey) {
                    e.preventDefault();
                    clearFormat();
                }
                break;
        }
    }
}

// Format text functions
function formatText(command) {
    const selection = window.getSelection();
    if (!selection.rangeCount) return;
    
    const range = selection.getRangeAt(0);
    const selectedText = range.toString();
    
    switch(command) {
        case 'bold':
            document.execCommand('bold', false, null);
            break;
        case 'italic':
            document.execCommand('italic', false, null);
            break;
        case 'underline':
            document.execCommand('underline', false, null);
            break;
        case 'strikethrough':
            document.execCommand('strikethrough', false, null);
            break;
        case 'heading1':
            if (selectedText) {
                const h1 = document.createElement('h1');
                h1.textContent = selectedText;
                range.deleteContents();
                range.insertNode(h1);
            }
            break;
        case 'heading2':
            if (selectedText) {
                const h2 = document.createElement('h2');
                h2.textContent = selectedText;
                range.deleteContents();
                range.insertNode(h2);
            }
            break;
        case 'heading3':
            if (selectedText) {
                const h3 = document.createElement('h3');
                h3.textContent = selectedText;
                range.deleteContents();
                range.insertNode(h3);
            }
            break;
        case 'unorderedList':
            document.execCommand('insertUnorderedList', false, null);
            break;
        case 'orderedList':
            document.execCommand('insertOrderedList', false, null);
            break;
        case 'blockquote':
            const blockquote = document.createElement('blockquote');
            blockquote.innerHTML = selectedText || '<p></p>';
            range.deleteContents();
            range.insertNode(blockquote);
            break;
    }
    
    // Update the hidden textarea
    const contentTextarea = document.getElementById('content');
    if (contentTextarea) {
        contentTextarea.value = editor.innerHTML;
    }
}

// Clear format function
function clearFormat() {
    const selection = window.getSelection();
    
    if (!selection.rangeCount) {
        // If no selection, clear all formatting from the entire content
        if (confirm('Are you sure you want to clear all formatting from the entire content? This action cannot be undone.')) {
            clearAllFormatting();
        }
        return;
    }
    
    const range = selection.getRangeAt(0);
    const selectedText = range.toString();
    
    if (selectedText) {
        // Clear formatting from selected text only
        clearSelectedFormatting(range);
    } else {
        // Clear formatting from the current element
        clearElementFormatting(range.commonAncestorContainer);
    }
    
    // Update the hidden textarea
    updateContent();
}

// Clear formatting from selected text
function clearSelectedFormatting(range) {
    // Get the selected content
    const selectedContent = range.cloneContents();
    const tempDiv = document.createElement('div');
    tempDiv.appendChild(selectedContent);
    
    // Remove all formatting tags but keep the text content
    let plainText = tempDiv.textContent || tempDiv.innerText || '';
    
    // Remove any extra whitespace but preserve basic formatting
    plainText = plainText.replace(/\s+/g, ' ').trim();
    
    // Create a new text node with the plain text
    const textNode = document.createTextNode(plainText);
    
    // Replace the selected content with plain text
    range.deleteContents();
    range.insertNode(textNode);
    
    // Normalize the text node to merge with adjacent text nodes
    const parent = textNode.parentNode;
    if (parent) {
        parent.normalize();
    }
}

// Clear formatting from an element
function clearElementFormatting(element) {
    // If the element is a text node, do nothing
    if (element.nodeType === Node.TEXT_NODE) {
        return;
    }
    
    // If the element is a formatting element, unwrap it
    const formattingElements = ['b', 'strong', 'i', 'em', 'u', 'strike', 'del', 'mark', 'code', 'a'];
    if (formattingElements.includes(element.tagName?.toLowerCase())) {
        unwrapElement(element);
        return;
    }
    
    // If it's a block element, clear its formatting
    const blockElements = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'ul', 'ol'];
    if (blockElements.includes(element.tagName?.toLowerCase())) {
        // Convert block elements to paragraphs
        const paragraph = document.createElement('p');
        paragraph.innerHTML = element.innerHTML;
        
        // Clear formatting from the paragraph content
        clearInlineFormatting(paragraph);
        
        // Replace the element
        element.parentNode.replaceChild(paragraph, element);
        return;
    }
    
    // Clear inline formatting from the element
    clearInlineFormatting(element);
}

// Clear all inline formatting from an element
function clearInlineFormatting(element) {
    const formattingElements = ['b', 'strong', 'i', 'em', 'u', 'strike', 'del', 'mark', 'code', 'a'];
    
    // Find all formatting elements within this element
    formattingElements.forEach(tagName => {
        const elements = element.querySelectorAll(tagName);
        elements.forEach(el => {
            unwrapElement(el);
        });
    });
}

// Unwrap an element (remove the tag but keep the content)
function unwrapElement(element) {
    const parent = element.parentNode;
    while (element.firstChild) {
        parent.insertBefore(element.firstChild, element);
    }
    parent.removeChild(element);
}

// Clear all formatting from the entire editor content
function clearAllFormatting() {
    if (!editor) return;
    
    // Get all the content as plain text
    const plainText = editor.textContent || editor.innerText || '';
    
    // Clear the editor and add the plain text
    editor.innerHTML = '';
    const paragraph = document.createElement('p');
    paragraph.textContent = plainText;
    editor.appendChild(paragraph);
}

// Insert code block
function insertCodeBlock() {
    const language = document.getElementById('codeLanguage').value || 'javascript';
    const theme = document.getElementById('codeTheme').value || 'default';
    const codeBlock = `
<pre class="code-block" data-language="${language}" data-theme="${theme}"><code class="language-${language}">
// Your code here
console.log('Hello, World!');
</code></pre>
`;
    
    insertHTML(codeBlock);
}

// Insert inline code
function insertInlineCode() {
    const selection = window.getSelection();
    if (selection.rangeCount) {
        const range = selection.getRangeAt(0);
        const selectedText = range.toString();
        const codeElement = document.createElement('code');
        codeElement.textContent = selectedText || 'inline code';
        range.deleteContents();
        range.insertNode(codeElement);
        updateContent();
    }
}

// Insert link
function insertLink() {
    const url = prompt('Enter URL:');
    if (url) {
        const text = prompt('Enter link text:', url);
        if (text) {
            const link = `<a href="${url}" target="_blank">${text}</a>`;
            insertHTML(link);
        }
    }
}

// Insert image
function insertImage() {
    const imageUrl = prompt('Enter image URL:');
    if (imageUrl) {
        const altText = prompt('Enter alt text:');
        const image = `<img src="${imageUrl}" alt="${altText || ''}" class="img-fluid">`;
        insertHTML(image);
    }
}

// Insert table
function insertTable() {
    const rows = prompt('Enter number of rows:', '3');
    const cols = prompt('Enter number of columns:', '3');
    
    if (rows && cols) {
        let tableHTML = '<table class="table table-bordered">';
        tableHTML += '<thead><tr>';
        
        for (let i = 0; i < cols; i++) {
            tableHTML += '<th>Header ' + (i + 1) + '</th>';
        }
        tableHTML += '</tr></thead><tbody>';
        
        for (let i = 0; i < rows - 1; i++) {
            tableHTML += '<tr>';
            for (let j = 0; j < cols; j++) {
                tableHTML += '<td>Cell ' + (i + 1) + '-' + (j + 1) + '</td>';
            }
            tableHTML += '</tr>';
        }
        tableHTML += '</tbody></table>';
        
        insertHTML(tableHTML);
    }
}

// Insert horizontal rule
function insertHorizontalRule() {
    insertHTML('<hr>');
}

// Insert HTML content
function insertHTML(html) {
    const selection = window.getSelection();
    if (selection.rangeCount) {
        const range = selection.getRangeAt(0);
        const div = document.createElement('div');
        div.innerHTML = html;
        
        // Convert HTML string to nodes
        const fragment = document.createDocumentFragment();
        while (div.firstChild) {
            fragment.appendChild(div.firstChild);
        }
        
        range.deleteContents();
        range.insertNode(fragment);
        updateContent();
    }
}

// Update content in hidden textarea
function updateContent() {
    const contentTextarea = document.getElementById('content');
    if (contentTextarea && editor) {
        contentTextarea.value = editor.innerHTML;
    }
}

// Toggle preview
function togglePreview() {
    const previewArea = document.getElementById('previewArea');
    const previewContent = document.getElementById('previewContent');
    
    if (!previewArea || !previewContent) return;
    
    if (previewArea.style.display === 'none') {
        previewContent.innerHTML = editor.innerHTML;
        previewArea.style.display = 'block';
    } else {
        previewArea.style.display = 'none';
    }
}

// Change code theme
function changeCodeTheme() {
    currentCodeTheme = document.getElementById('codeTheme').value;
    // Update existing code blocks
    const codeBlocks = editor.querySelectorAll('.code-block');
    codeBlocks.forEach(block => {
        block.setAttribute('data-theme', currentCodeTheme);
    });
}

// Change code language
function changeCodeLanguage() {
    currentCodeLanguage = document.getElementById('codeLanguage').value;
}

// Enhanced media insertion
function insertMedia() {
    const modal = new bootstrap.Modal(document.getElementById('mediaPickerModal'));
    modal.show();
    loadMediaFiles();
}

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="POST"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Ensure the content is synced before submission
            const editor = document.getElementById('editor');
            const contentTextarea = document.getElementById('content');
            if (editor && contentTextarea) {
                contentTextarea.value = editor.innerHTML;
            }
        });
    }
});

// Enhanced delete post function
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'posts.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const postIdInput = document.createElement('input');
        postIdInput.type = 'hidden';
        postIdInput.name = 'post_id';
        postIdInput.value = postId;
        
        form.appendChild(actionInput);
        form.appendChild(postIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Enhanced media functions
function loadMediaFiles() {
    fetch('media.php?action=get_media')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMediaFiles(data.media);
            } else {
                console.error('Error loading media:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function displayMediaFiles(mediaFiles) {
    const container = document.getElementById('mediaFilesContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (mediaFiles.length === 0) {
        container.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-inbox fa-2x mb-2"></i><p>No media files found</p></div>';
        return;
    }
    
    mediaFiles.forEach(media => {
        const mediaItem = document.createElement('div');
        mediaItem.className = 'col-md-3 col-sm-4 col-6 mb-3';
        mediaItem.innerHTML = `
            <div class="card media-item" onclick="selectMedia('${media.file_path}', '${media.original_name}', '${media.alt_text || ''}')">
                <div class="card-body p-2 text-center">
                    ${isImageFile(media.file_type) ? 
                        `<img src="${media.file_path}" class="img-fluid" alt="${media.original_name}" style="max-height: 100px;">` :
                        `<i class="fas fa-file fa-3x text-muted"></i>`
                    }
                    <div class="mt-2">
                        <small class="text-muted">${media.original_name}</small>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(mediaItem);
    });
}

function isImageFile(fileType) {
    return fileType && fileType.startsWith('image/');
}

function selectMedia(filePath, fileName, altText) {
    const imageHTML = `<img src="${filePath}" alt="${altText || fileName}" class="img-fluid">`;
    insertHTML(imageHTML);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('mediaPickerModal'));
    if (modal) {
        modal.hide();
    }
}

// Clear formatting
function clearFormat() {
    const selection = window.getSelection();
    if (selection.rangeCount) {
        const range = selection.getRangeAt(0);
        const selectedNode = range.commonAncestorContainer;
        
        // If the selected node is a block element (like a heading, list, blockquote),
        // we need to remove its children to clear the formatting.
        if (selectedNode.nodeType === Node.ELEMENT_NODE) {
            const children = Array.from(selectedNode.children);
            children.forEach(child => {
                if (child.nodeType === Node.ELEMENT_NODE) {
                    // Remove specific formatting elements
                    if (child.tagName === 'H1' || child.tagName === 'H2' || child.tagName === 'H3') {
                        child.remove();
                    }
                    if (child.tagName === 'UL' || child.tagName === 'OL') {
                        child.remove();
                    }
                    if (child.tagName === 'BLOCKQUOTE') {
                        child.remove();
                    }
                    if (child.tagName === 'PRE') {
                        child.remove();
                    }
                    if (child.tagName === 'CODE') {
                        child.remove();
                    }
                    if (child.tagName === 'A') {
                        child.removeAttribute('href');
                        child.removeAttribute('target');
                    }
                    if (child.tagName === 'IMG') {
                        child.removeAttribute('src');
                        child.removeAttribute('alt');
                    }
                    if (child.tagName === 'TABLE') {
                        child.remove();
                    }
                    if (child.tagName === 'HR') {
                        child.remove();
                    }
                }
            });
        }
        
        // If the selected node is a text node, remove its formatting
        if (selectedNode.nodeType === Node.TEXT_NODE) {
            const parent = selectedNode.parentElement;
            if (parent && parent.tagName === 'PRE') {
                parent.remove();
            }
        }
        
        // After clearing, re-apply the current formatting to the selection
        // This is a simplified approach; a more robust solution might involve
        // re-evaluating the selection's formatting attributes.
        // For now, we just remove the formatting.
    }
}
</script>

<!-- Media Picker Modal -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-labelledby="mediaPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPickerModalLabel">
                    <i class="fas fa-images me-2"></i>Media Library
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="mediaFilesContainer">
                    <!-- Media files will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="media.php" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i>Upload New Media
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?>