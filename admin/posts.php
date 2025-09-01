<?php
require_once '../config/session.php';
require_once '../includes/User.php';
require_once '../includes/Post.php';
require_once '../includes/Category.php';
require_once '../includes/Tag.php'; // Added Tag.php
require_once '../includes/Course.php';

$user = new User();
$post = new Post();
$category = new Category();
$tag = new Tag(); // Added Tag object
$course = new Course();

// Check if user is logged in and is admin
if (!$user->isLoggedIn()) {
    header('Location: ../index');
    exit;
}

$currentUser = $user->getCurrentUser();
if (!$currentUser || !$currentUser['is_admin']) {
    header('Location: ../index');
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
            $courseModuleId = !empty($_POST['course_module_id']) ? $_POST['course_module_id'] : null;
            $lessonOrder = $_POST['lesson_order'] ?? 1;
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
                'course_module_id' => $courseModuleId,
                'lesson_order' => $lessonOrder,
                'status' => $status,
                'is_premium' => $isPremium,
                'is_featured' => $isFeatured,
                'author_id' => $currentUser['id'],
                'tags' => $tags
            ]);
            
            // Debug: Log the result
            error_log("Post creation result: " . print_r($result, true));
            
            if ($result['success']) {
                header('Location: posts?message=' . urlencode($result['message']));
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
                header('Location: posts?message=' . urlencode($result['message']));
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
            $courseModuleId = !empty($_POST['course_module_id']) ? $_POST['course_module_id'] : null;
            $lessonOrder = $_POST['lesson_order'] ?? 1;
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
                'course_module_id' => $courseModuleId,
                'lesson_order' => $lessonOrder,
                'status' => $status,
                'is_premium' => $isPremium,
                'is_featured' => $isFeatured,
                'tags' => $tags
            ]);
            
            // Debug: Log the result
            error_log("Post update result: " . print_r($result, true));
            
            if ($result['success']) {
                header('Location: posts?message=' . urlencode($result['message']));
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
                    <a href="posts?action=add" class="btn btn-sm btn-primary">
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
                            <th>Course/Module</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allPosts)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No posts found. <a href="posts?action=add">Create your first post</a></p>
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
                                <?php if (isset($postItem['course_module_id']) && $postItem['course_module_id']): ?>
                                    <?php 
                                    $moduleInfo = $course->getModuleById($postItem['course_module_id']);
                                    if ($moduleInfo):
                                        $courseInfo = $course->getCourseById($moduleInfo['course_id']);
                                    ?>
                                        <div class="course-info">
                                            <small class="text-primary fw-bold">
                                                <i class="fas fa-graduation-cap me-1"></i>
                                                <?php echo htmlspecialchars($courseInfo['title']); ?>
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-book me-1"></i>
                                                <?php echo htmlspecialchars($moduleInfo['title']); ?>
                                            </small>
                                            <?php if (isset($postItem['lesson_order']) && $postItem['lesson_order']): ?>
                                                <br>
                                                <small class="badge bg-secondary">
                                                    Lesson #<?php echo $postItem['lesson_order']; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="fas fa-minus me-1"></i>
                                        Not in course
                                    </span>
                                <?php endif; ?>
                            </td>
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
                                <a href="posts?action=edit&id=<?php echo $postItem['id']; ?>" class="btn btn-sm btn-outline-primary">
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <div>
                    <h1 class="h2 mb-0">
                        <i class="fas fa-plus-circle text-primary me-2"></i>Create New Post
                    </h1>
                    <p class="text-muted mb-0">Write and publish your next great article</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="posts" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Posts
                    </a>
                    <button type="submit" form="addPostForm" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Post
                    </button>
                </div>
            </div>

            <form method="POST" action="posts?action=add" id="addPostForm">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Main Content Section -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>Post Content
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="title" class="form-label fw-bold">
                                        <i class="fas fa-heading me-1"></i>Post Title
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                           placeholder="Enter an engaging title for your post..." required>
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>Make it catchy and descriptive to attract readers
                                    </div>
                        </div>

                                <div class="mb-4">
                                    <label for="excerpt" class="form-label fw-bold">
                                        <i class="fas fa-quote-left me-1"></i>Excerpt
                                    </label>
                                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                              placeholder="Write a brief summary of your post..."></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>This will appear in post previews and search results
                                    </div>
                        </div>

                                <div class="mb-4">
                                    <label for="content" class="form-label fw-bold">
                                        <i class="fas fa-pen-fancy me-1"></i>Post Content
                                    </label>
                            <div class="rich-text-editor">
                                        <!-- Enhanced Editor Toolbar -->
                                        <div class="editor-toolbar mb-3 p-3 bg-light border rounded">
                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                <!-- Text Formatting -->
                                    <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="formatText('bold')" title="Bold (Ctrl+B)">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="formatText('italic')" title="Italic (Ctrl+I)">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="formatText('underline')" title="Underline (Ctrl+U)">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="formatText('strikethrough')" title="Strikethrough">
                                            <i class="fas fa-strikethrough"></i>
                                        </button>
                                    </div>
                                    
                                                <div class="vr mx-2"></div>
                                                
                                                <!-- Headings -->
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="formatText('heading1')" title="Heading 1">
                                            <i class="fas fa-heading"></i>1
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="formatText('heading2')" title="Heading 2">
                                            <i class="fas fa-heading"></i>2
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="formatText('heading3')" title="Heading 3">
                                            <i class="fas fa-heading"></i>3
                                        </button>
                                    </div>
                                    
                                                <div class="vr mx-2"></div>
                                                
                                                <!-- Lists and Quotes -->
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="formatText('unorderedList')" title="Unordered List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="formatText('orderedList')" title="Ordered List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="formatText('blockquote')" title="Blockquote">
                                            <i class="fas fa-quote-left"></i>
                                        </button>
                                    </div>
                                    
                                                <div class="vr mx-2"></div>
                                                
                                                <!-- Code and Links -->
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="insertCodeBlock()" title="Code Block">
                                            <i class="fas fa-code"></i>
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="insertInlineCode()" title="Inline Code">
                                            <i class="fas fa-code"></i> Inline
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="editSelectedCodeBlockTitle()" title="Edit Code Block Title">
                                            <i class="fas fa-heading"></i> Title
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="insertLink()" title="Insert Link (Ctrl+K)">
                                            <i class="fas fa-link"></i>
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="insertImage()" title="Insert Image">
                                            <i class="fas fa-image"></i>
                                        </button>
                                    </div>
                                    
                                                <div class="vr mx-2"></div>
                                                
                                                <!-- Advanced Features -->
                                                <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTable()" title="Insert Table">
                                            <i class="fas fa-table"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertHorizontalRule()" title="Horizontal Rule">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFormat()" title="Clear Format (Ctrl+Shift+X)">
                                                        <i class="fas fa-eraser"></i>
                                        </button>
                                    </div>
                                    
                                                <div class="vr mx-2"></div>
                                                
                                                <!-- Preview and Settings -->
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="togglePreview()" title="Toggle Preview">
                                                        <i class="fas fa-eye"></i> Preview
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="applySyntaxHighlighting()" title="Apply Syntax Highlighting">
                                                        <i class="fas fa-palette"></i> Highlight
                                                    </button>
                                                </div>
                                                
                                                <div class="vr mx-2"></div>
                                                
                                                <!-- Code Settings -->
                                                <div class="d-flex gap-2">
                                                    <select class="form-select form-select-sm" id="codeTheme" onchange="changeCodeTheme()" style="width: auto;">
                                                        <option value="default">Theme</option>
                                            <option value="monokai">Monokai</option>
                                            <option value="dracula">Dracula</option>
                                            <option value="material">Material</option>
                                        </select>
                                                    <select class="form-select form-select-sm" id="codeLanguage" onchange="changeCodeLanguage()" style="width: auto;">
                                                        <option value="javascript">JS</option>
                                            <option value="python">Python</option>
                                            <option value="php">PHP</option>
                                            <option value="html">HTML</option>
                                            <option value="css">CSS</option>
                                            <option value="sql">SQL</option>
                                            <option value="bash">Bash</option>
                                        </select>
                                    </div>
                                </div>
                                        </div>
                                <!-- Enhanced Editor Content Area -->
                                <div class="editor-container">
                                    <div id="editor" class="editor-content border rounded p-3" contenteditable="true" 
                                         style="min-height: 400px; background-color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6;">
                                        <p class="text-muted">Start writing your content here...</p>
                                    </div>
                                    
                                    <!-- Hidden textarea for form submission -->
                                    <textarea class="form-control" id="content" name="content" rows="20" style="display: none;" required></textarea>
                                </div>
                                
                                <!-- Enhanced Preview Area -->
                                <div id="previewArea" class="preview-content mt-3" style="display: none;">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-eye me-2"></i>Live Preview
                                            </h6>
                                        </div>
                                        <div class="card-body" id="previewContent" style="min-height: 200px; background-color: #f8f9fa;">
                                            <p class="text-muted text-center">Preview will appear here...</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Enhanced Help Text -->
                                <div class="alert alert-info mt-3">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>Editor Tips
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Keyboard Shortcuts:</strong>
                                            <ul class="mb-0 mt-1">
                                                <li><kbd>Ctrl+B</kbd> for <strong>Bold</strong></li>
                                                <li><kbd>Ctrl+I</kbd> for <em>Italic</em></li>
                                                <li><kbd>Ctrl+U</kbd> for <u>Underline</u></li>
                                                <li><kbd>Ctrl+K</kbd> for <a href="#">Link</a></li>
                                                <li><kbd>Ctrl+Shift+X</kbd> to clear formatting</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Pro Tips:</strong>
                                            <ul class="mb-0 mt-1">
                                                <li>Use headings to structure your content</li>
                                                <li>Add code blocks for technical content</li>
                                                <li>Right-click code blocks to edit titles</li>
                                                <li>Include images to make posts engaging</li>
                                                <li>Preview before publishing</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div class="col-lg-4">
                        <!-- Post Settings Section -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-cog me-2"></i>Post Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="category_id" class="form-label fw-bold">
                                        <i class="fas fa-folder me-1"></i>Category
                                    </label>
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
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>Choose the most appropriate category for your post
                                    </div>
                        </div>

                        <!-- Course Module Selection -->
                        <div class="mb-4">
                            <label for="new_course_module_id" class="form-label fw-bold">
                                <i class="fas fa-graduation-cap me-1"></i>Course Module (Optional)
                            </label>
                            <select class="form-select" id="new_course_module_id" name="course_module_id">
                                <option value="">Not part of a course</option>
                                <?php
                                // Get all courses with modules
                                $allCourses = $course->getAllCourses(true);
                                foreach ($allCourses as $courseItem):
                                    $modules = $course->getModulesByCourse($courseItem['id'], true);
                                    if (!empty($modules)):
                                ?>
                                    <optgroup label="<?php echo htmlspecialchars($courseItem['title']); ?>">
                                        <?php foreach ($modules as $module): ?>
                                            <option value="<?php echo $module['id']; ?>">
                                                <?php echo htmlspecialchars($module['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Link this post to a course module for progressive learning
                            </div>
                        </div>

                        <div class="mb-4" id="new-lesson-order-container" style="display: none;">
                            <label for="new_lesson_order" class="form-label fw-bold">
                                <i class="fas fa-sort-numeric-up me-1"></i>Lesson Order
                            </label>
                            <input type="number" class="form-control" id="new_lesson_order" name="lesson_order" 
                                   value="1" min="1" max="100">
                            <div class="form-text">Order of this lesson within the module (1 = first lesson)</div>
                        </div>

                                <div class="mb-4">
                                    <label for="tags" class="form-label fw-bold">
                                        <i class="fas fa-tags me-1"></i>Tags
                                    </label>
                                    <input type="text" class="form-control" id="tags" name="tags" 
                                           placeholder="Enter tags separated by commas">
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>Add relevant tags to help readers find your post
                                    </div>
                                    
                                    <!-- Available Tags -->
                                    <div class="mt-3">
                                        <label class="form-label small fw-bold text-muted">
                                            <i class="fas fa-tag me-1"></i>Available Tags:
                                        </label>
                                        <div class="tag-cloud p-2 bg-light rounded">
                                    <?php
                                    $allTags = $tag->getAllTagsWithProperties();
                                    foreach ($allTags as $tagItem):
                                    ?>
                                            <span class="badge me-1 mb-1" 
                                                  style="background-color: <?php echo htmlspecialchars($tagItem['color']); ?>; color: white; cursor: pointer;" 
                                                  onclick="addTagToInput('<?php echo htmlspecialchars($tagItem['name']); ?>')"
                                                  title="Click to add this tag">
                                        <?php echo htmlspecialchars($tagItem['name']); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                                <div class="mb-4">
                                    <label for="status" class="form-label fw-bold">
                                        <i class="fas fa-toggle-on me-1"></i>Publication Status
                                    </label>
                            <select class="form-select" id="status" name="status">
                                        <option value="draft">
                                            <i class="fas fa-save"></i> Draft
                                        </option>
                                        <option value="published">
                                            <i class="fas fa-globe"></i> Published
                                        </option>
                            </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>Draft posts are saved but not visible to readers
                                    </div>
                        </div>

                                <!-- Post Options -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-star me-1"></i>Post Options
                                    </label>
                                    <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium">
                                        <label class="form-check-label" for="is_premium">
                                            <i class="fas fa-crown text-warning me-1"></i>Premium Content
                                        </label>
                                        <div class="form-text small">
                                            Premium posts are only visible to premium subscribers
                                        </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                        <label class="form-check-label" for="is_featured">
                                            <i class="fas fa-star text-warning me-1"></i>Featured Post
                                        </label>
                                        <div class="form-text small">
                                            Featured posts appear prominently on the homepage
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Create Post
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="saveAsDraft()">
                                        <i class="fas fa-save me-2"></i>Save as Draft
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="togglePreview()">
                                        <i class="fas fa-eye me-2"></i>Preview Post
                            </button>
                                </div>
                            </div>
                        </div>

                        <!-- Post Statistics -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Post Statistics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h4 class="text-primary mb-0" id="wordCount">0</h4>
                                            <small class="text-muted">Words</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-0" id="charCount">0</h4>
                                        <small class="text-muted">Characters</small>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>Estimated reading time: <span id="readingTime">0 min</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Custom CSS for Enhanced Form -->
            <style>
            .editor-toolbar .btn-group .btn {
                transition: all 0.2s ease;
            }
            
            .editor-toolbar .btn-group .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .editor-content:focus {
                outline: none;
                border-color: #007bff !important;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }
            
            .tag-cloud .badge {
                transition: all 0.2s ease;
            }
            
            .tag-cloud .badge:hover {
                transform: scale(1.05);
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }
            
            .card {
                transition: all 0.3s ease;
            }
            
            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            
            .form-control:focus, .form-select:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }
            
            .btn-lg {
                padding: 0.75rem 1.5rem;
                font-size: 1.1rem;
            }
            
            /* Enhanced Code Block Styling */
            .code-block {
                background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
                border: 1px solid #4a5568;
                border-radius: 8px;
                padding: 1.5rem;
                margin: 1rem 0;
                position: relative;
                overflow-x: auto;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            
            .code-block::before {
                content: 'CODE';
                position: absolute;
                top: 0.5rem;
                right: 1rem;
                background: #4299e1;
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 4px;
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }
            
            .code-block pre {
                margin: 0;
                color: #e2e8f0;
                font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
                font-size: 0.9rem;
                line-height: 1.6;
                text-shadow: none;
            }
            
            .code-block code {
                background: transparent;
                color: inherit;
                padding: 0;
                border: none;
                font-size: inherit;
            }
            
            /* Inline Code Styling */
            .inline-code {
                background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
                color: #2d3748;
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
                font-size: 0.9em;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                transition: all 0.2s ease;
            }
            
            .inline-code:hover {
                background: linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%);
                border-color: #cbd5e0;
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            /* Code Block Language Indicators */
            .code-block[data-language="html"]::before { content: 'HTML'; background: #e53e3e; }
            .code-block[data-language="css"]::before { content: 'CSS'; background: #3182ce; }
            .code-block[data-language="javascript"]::before { content: 'JS'; background: #d69e2e; }
            .code-block[data-language="php"]::before { content: 'PHP'; background: #805ad5; }
            .code-block[data-language="sql"]::before { content: 'SQL'; background: #38a169; }
            .code-block[data-language="python"]::before { content: 'PY'; background: #2b6cb0; }
            .code-block[data-language="java"]::before { content: 'JAVA'; background: #dd6b20; }
            .code-block[data-language="csharp"]::before { content: 'C#'; background: #319795; }
            
            /* Syntax Highlighting Colors */
            .code-block .keyword { color: #ff79c6; }
            .code-block .string { color: #f1fa8c; }
            .code-block .comment { color: #6272a4; font-style: italic; }
            .code-block .function { color: #50fa7b; }
            .code-block .number { color: #bd93f9; }
            .code-block .operator { color: #ff79c6; }
            .code-block .class { color: #8be9fd; }
            .code-block .variable { color: #f8f8f2; }
            .code-block .tag { color: #ff79c6; }
            .code-block .property { color: #50fa7b; }
            
            /* Code Block Enhancements */
            .code-block {
                position: relative;
                overflow: hidden;
            }
            
            /* Code Block Title Styling */
            .code-block .code-title {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 12px 16px;
                border-radius: 8px 8px 0 0;
                font-size: 14px;
                font-weight: 600;
                margin: -1.5rem -1.5rem 1rem -1.5rem;
                border-bottom: 1px solid #4a5568;
                text-align: center;
                letter-spacing: 0.5px;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            }
            
            .code-block .code-title:hover {
                background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
                cursor: pointer;
            }
            
            .code-block::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1px;
                background: linear-gradient(90deg, transparent, #4a5568, transparent);
            }
            
            .code-block pre {
                position: relative;
                padding-left: 1rem;
            }
            
            .code-block pre::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 1px;
                background: linear-gradient(180deg, transparent, #4a5568, transparent);
            }
            
            /* Copy Button for Code Blocks */
            .code-block .copy-btn {
                position: absolute;
                top: 0.5rem;
                right: 3rem;
                background: #4299e1;
                color: white;
                border: none;
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                font-size: 0.7rem;
                cursor: pointer;
                opacity: 0;
                transition: opacity 0.2s ease;
            }
            
            .code-block:hover .copy-btn {
                opacity: 1;
            }
            
            .code-block .copy-btn:hover {
                background: #3182ce;
                transform: scale(1.05);
            }
            
            /* Inline Code Enhancements */
            .inline-code {
                position: relative;
                overflow: hidden;
            }
            
            .inline-code::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .inline-code:hover::before {
                transform: translateX(100%);
            }
            
            /* Code Block Typography and Spacing */
            .code-block pre {
                font-size: 0.9rem;
                line-height: 1.6;
                letter-spacing: 0.3px;
            }
            
            .code-block code {
                font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
                font-weight: 400;
            }
            
            /* Code Block Responsive Design */
            @media (max-width: 768px) {
                .code-block {
                    padding: 1rem;
                    margin: 0.5rem 0;
                }
                
                .code-block pre {
                    font-size: 0.8rem;
                    padding-left: 0.5rem;
                }
                
                .code-block::before {
                    font-size: 0.7rem;
                    padding: 0.2rem 0.5rem;
                    right: 0.5rem;
                }
                
                .code-block .copy-btn {
                    right: 2.5rem;
                    font-size: 0.6rem;
                    padding: 0.2rem 0.4rem;
                }
            }
            
            /* Code Block Focus States */
            .code-block:focus-within {
                border-color: #4299e1;
                box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25);
            }
            
            /* Inline Code Focus States */
            .inline-code:focus {
                outline: 2px solid #4299e1;
                outline-offset: 2px;
            }
            </style>

            <!-- Enhanced JavaScript for Form Features -->
            <script>
            // Word count and reading time calculation
            function updateWordCount() {
                const editor = document.getElementById('editor');
                const text = editor.textContent || editor.innerText || '';
                const words = text.trim().split(/\s+/).filter(word => word.length > 0);
                const characters = text.length;
                
                document.getElementById('wordCount').textContent = words.length;
                document.getElementById('charCount').textContent = characters;
                
                // Calculate reading time (average 200 words per minute)
                const readingTime = Math.ceil(words.length / 200);
                document.getElementById('readingTime').textContent = readingTime + ' min';
            }
            
            // Save as draft functionality
            function saveAsDraft() {
                document.getElementById('status').value = 'draft';
                document.getElementById('addPostForm').submit();
            }
            
            // Enhanced tag input
            function addTagToInput(tagName) {
                const tagInput = document.getElementById('tags');
                const currentTags = tagInput.value;
                
                if (currentTags) {
                    // Check if tag already exists
                    const tags = currentTags.split(',').map(tag => tag.trim());
                    if (!tags.includes(tagName)) {
                        tagInput.value = currentTags + ', ' + tagName;
                    }
                } else {
                    tagInput.value = tagName;
                }
                
                // Highlight the input
                tagInput.focus();
                tagInput.style.borderColor = '#28a745';
                setTimeout(() => {
                    tagInput.style.borderColor = '';
                }, 2000);
            }
            
            // Real-time word count updates
            document.addEventListener('DOMContentLoaded', function() {
                const editor = document.getElementById('editor');
                if (editor) {
                    editor.addEventListener('input', updateWordCount);
                    editor.addEventListener('keyup', updateWordCount);
                    updateWordCount(); // Initial count
                    
                    // Apply syntax highlighting to existing code blocks
                    setTimeout(() => {
                        applySyntaxHighlighting();
                    }, 500);
                    
                    // Initialize code block context menu
                    addCodeBlockContextMenu();
                }
            });
            </script>

            <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
            <!-- Edit Post Form -->
            <?php
            $postId = $_GET['id'];
            $postData = $post->getPostById($postId);
            
            if (!$postData) {
                echo '<div class="alert alert-danger">Post not found.</div>';
            } else {
            ?>
            <form method="POST" action="posts?action=edit">
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
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="pasteHtmlContent()" title="Paste HTML Content">
                                            <i class="fas fa-paste"></i> HTML
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertJavaScriptGuide()" title="Insert JavaScript Guide Template">
                                            <i class="fas fa-file-code"></i> JS Guide
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSelectedCodeBlockTitle()" title="Edit Code Block Title">
                                            <i class="fas fa-heading"></i> Title
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
                                    Use the toolbar buttons to format your content. You can also use keyboard shortcuts like <kbd>Ctrl+B</kbd> for bold, <kbd>Ctrl+I</kbd> for italic, <kbd>Ctrl+U</kbd> for underline, <kbd>Ctrl+K</kbd> for link, and <kbd>Ctrl+Shift+X</kbd> to clear formatting. <strong>Pro tip:</strong> Right-click code blocks to edit titles!
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

                        <!-- Course Module Selection -->
                        <div class="mb-3">
                            <label for="course_module_id" class="form-label">
                                <i class="fas fa-graduation-cap me-1"></i>Course Module (Optional)
                            </label>
                            <select class="form-select" id="course_module_id" name="course_module_id">
                                <option value="">Not part of a course</option>
                                <?php
                                // Get all courses with modules
                                $allCourses = $course->getAllCourses(true);
                                foreach ($allCourses as $courseItem):
                                    $modules = $course->getModulesByCourse($courseItem['id'], true);
                                    if (!empty($modules)):
                                ?>
                                    <optgroup label="<?php echo htmlspecialchars($courseItem['title']); ?>">
                                        <?php foreach ($modules as $module): ?>
                                            <option value="<?php echo $module['id']; ?>" 
                                                    <?php echo (isset($postData['course_module_id']) && $module['id'] == $postData['course_module_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($module['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Link this post to a course module for progressive learning
                            </div>
                        </div>

                        <div class="mb-3" id="lesson-order-container" style="display: none;">
                            <label for="lesson_order" class="form-label">
                                <i class="fas fa-sort-numeric-up me-1"></i>Lesson Order
                            </label>
                            <input type="number" class="form-control" id="lesson_order" name="lesson_order" 
                                   value="<?php echo isset($postData['lesson_order']) ? $postData['lesson_order'] : 1; ?>" 
                                   min="1" max="100">
                            <div class="form-text">Order of this lesson within the module (1 = first lesson)</div>
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
                            <a href="posts" class="btn btn-outline-secondary">
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
    
    // Handle paste events to properly process HTML content
    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        
        // Get pasted content
        let pastedContent = '';
        if (e.clipboardData && e.clipboardData.getData) {
            pastedContent = e.clipboardData.getData('text/html') || e.clipboardData.getData('text/plain');
        } else if (window.clipboardData && window.clipboardData.getData) {
            pastedContent = window.clipboardData.getData('Text');
        }
        
        if (pastedContent) {
            // Clean and process the pasted HTML
            const cleanedContent = cleanPastedHtml(pastedContent);
            
            // Insert at cursor position
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                range.deleteContents();
                
                // Create a temporary div to parse the HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = cleanedContent;
                
                // Insert the cleaned content
                const fragment = document.createDocumentFragment();
                while (tempDiv.firstChild) {
                    fragment.appendChild(tempDiv.firstChild);
                }
                range.insertNode(fragment);
                
                // Update textarea
                if (contentTextarea) {
                    contentTextarea.value = editor.innerHTML;
                }
            }
        }
    });
    
    // Add keyboard shortcuts
    editor.addEventListener('keydown', function(e) {
        handleKeyboardShortcuts(e);
    });
    
    // Focus the editor
    editor.focus();
}

// Paste HTML content function
function pasteHtmlContent() {
    const htmlContent = prompt('Paste your HTML content here:');
    if (htmlContent && htmlContent.trim()) {
        const cleanedContent = cleanPastedHtml(htmlContent.trim());
        
        // Insert at cursor position
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            range.deleteContents();
            
            // Create a temporary div to parse the HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = cleanedContent;
            
            // Insert the cleaned content
            const fragment = document.createDocumentFragment();
            while (tempDiv.firstChild) {
                fragment.appendChild(tempDiv.firstChild);
            }
            range.insertNode(fragment);
            
            // Update textarea
            const contentTextarea = document.getElementById('content');
            if (contentTextarea) {
                contentTextarea.value = editor.innerHTML;
            }
            
            // Apply syntax highlighting
            applySyntaxHighlighting();
        }
    }
}

// Insert sample JavaScript guide content
function insertJavaScriptGuide() {
    const sampleContent = `<h2>The Problem That Started It All</h2>

<p>Last year, I was working on a React project for a fintech startup, and my team lead kept pushing me to "use more modern JavaScript." I was still writing ES5-style code with <code>var</code> declarations and <code>function</code> keywords everywhere. Honestly, I thought my code was fine - it worked, right? But then came the code review.</p>

<p>My teammate Sarah (who's been coding in JavaScript for 8 years) left a comment that changed everything: "This looks like it was written in 2010. Have you heard of ES6?" I was embarrassed, but more than that, I was curious. What was I missing?</p>

<h2>What I Tried First (And Why It Failed)</h2>

<p>I started by reading the official ECMAScript documentation. Big mistake. It's like trying to learn cooking by reading a chemistry textbook. I got lost in the technical jargon and gave up after 30 minutes.</p>

<p>Then I tried watching YouTube tutorials. While some were helpful, most just showed the syntax without explaining why you'd use one approach over another. I was copying code without understanding the reasoning behind it.</p>

<p>Finally, I decided to learn by doing. I refactored one of my existing projects, line by line, replacing old patterns with new ones. This approach actually worked, but it took me weeks to figure out the best practices.</p>

<h2>The ES6+ Features That Actually Changed My Development</h2>

<h3>1. Arrow Functions: More Than Just Shorter Syntax</h3>

<p>When I first saw arrow functions, I thought they were just a way to write shorter functions. Boy, was I wrong.</p>

<div class="code-block" data-title="Arrow Functions Comparison">
<pre><code class="language-javascript">// Old way (ES5)
var self = this;
var button = document.getElementById('submit');
button.addEventListener('click', function() {
    self.handleClick();
});

// New way (ES6+)
const button = document.getElementById('submit');
button.addEventListener('click', () => {
    this.handleClick();
});</code></pre>
</div>

<p>But here's what I learned the hard way: arrow functions don't have their own <code>this</code> context. This actually bit me in production when I was trying to use <code>this.setState()</code> in a React component with an arrow function. The error message was cryptic, and I spent an entire afternoon debugging it.</p>

<p><strong>Pro tip</strong>: Use arrow functions for callbacks and when you want to preserve the <code>this</code> context, but stick with regular functions for methods that need their own <code>this</code>.</p>

<h3>2. Template Literals: The String Revolution</h3>

<p>I used to hate concatenating strings in JavaScript. It was ugly, error-prone, and made my code hard to read. Then I discovered template literals.</p>

<div class="code-block" data-title="Template Literals Example">
<pre><code class="language-javascript">// Old way (ES5)
var name = 'John';
var age = 30;
var message = 'Hello, my name is ' + name + ' and I am ' + age + ' years old.';

// New way (ES6+)
const name = 'John';
const age = 30;
const message = \`Hello, my name is \${name} and I am \${age} years old.\`;</code></pre>
</div>

<p>But here's where it gets interesting - template literals can contain expressions, not just variables. I once used this to create a dynamic SQL query builder:</p>

<div class="code-block" data-title="Dynamic Query Builder">
<pre><code class="language-javascript">const buildQuery = (table, conditions) => {
    const whereClause = conditions.map(cond => 
        \`\${cond.field} = '\${cond.value}'\`
    ).join(' AND ');
    
    return \`SELECT * FROM \${table} WHERE \${whereClause}\`;
};</code></pre>
</div>

<p><strong>Warning</strong>: Be careful with user input in template literals - they can still be vulnerable to SQL injection if you're not careful. I learned this lesson during a security audit.</p>

<h3>3. Destructuring: The Art of Unpacking</h3>

<p>Destructuring seemed like magic when I first saw it. Being able to extract values from objects and arrays with such clean syntax felt revolutionary.</p>

<div class="code-block" data-title="Destructuring Examples">
<pre><code class="language-javascript">// Old way (ES5)
var user = { name: 'John', email: 'john@example.com', age: 30 };
var name = user.name;
var email = user.email;
var age = user.age;

// New way (ES6+)
const user = { name: 'John', email: 'john@example.com', age: 30 };
const { name, email, age } = user;</code></pre>
</div>

<p>But here's a trick I discovered that saved me hours of debugging: you can destructure with default values and aliases.</p>

<div class="code-block" data-title="Destructuring with Defaults">
<pre><code class="language-javascript">const { name, email, age = 25, role: userRole = 'user' } = user;
console.log(userRole); // 'user' (if role doesn't exist in user object)</code></pre>
</div>

<p>I use this pattern all the time when working with API responses that might have missing fields.</p>

<h3>4. Spread and Rest Operators: The Swiss Army Knife</h3>

<p>The spread operator (<code>...</code>) became my favorite ES6+ feature. It's incredibly versatile and makes your code much cleaner.</p>

<div class="code-block" data-title="Spread Operator Examples">
<pre><code class="language-javascript">// Combining arrays
const frontend = ['React', 'Vue', 'Angular'];
const backend = ['Node.js', 'Express', 'MongoDB'];
const fullstack = [...frontend, ...backend];

// Copying objects
const user = { name: 'John', email: 'john@example.com' };
const userWithRole = { ...user, role: 'admin' };

// Function arguments
const sum = (...numbers) => numbers.reduce((total, num) => total + num, 0);</code></pre>
</div>

<p>Here's a real example from my current project - I use it to merge configuration objects:</p>

<div class="code-block" data-title="Configuration Merging">
<pre><code class="language-javascript">const defaultConfig = {
    apiUrl: 'https://api.example.com',
    timeout: 5000,
    retries: 3
};

const userConfig = {
    apiUrl: 'https://custom-api.example.com',
    timeout: 10000
};

const finalConfig = { ...defaultConfig, ...userConfig };
// Result: { apiUrl: 'https://custom-api.example.com', timeout: 10000, retries: 3 }</code></pre>
</div>

<h3>5. Async/Await: Finally, Readable Asynchronous Code</h3>

<p>This is the feature that made me fall in love with modern JavaScript. Before async/await, I was drowning in callback hell and Promise chains.</p>

<div class="code-block" data-title="Async/Await vs Promises">
<pre><code class="language-javascript">// Old way with Promises
fetch('/api/users')
    .then(response => response.json())
    .then(users => {
        return fetch(\`/api/users/\${users[0].id}/posts\`);
    })
    .then(response => response.json())
    .then(posts => {
        console.log(posts);
    })
    .catch(error => {
        console.error('Error:', error);
    });

// New way with async/await
try {
    const response = await fetch('/api/users');
    const users = await response.json();
    const postsResponse = await fetch(\`/api/users/\${users[0].id}/posts\`);
    const posts = await postsResponse.json();
    console.log(posts);
} catch (error) {
    console.error('Error:', error);
}</code></pre>
</div>

<p>But here's what I learned about async/await: you need to handle errors properly. I once forgot to wrap my async code in a try-catch block, and when the API went down, my entire app crashed silently. Not fun.</p>

<h2>Common Pitfalls I Encountered (And How to Avoid Them)</h2>

<h3>1. Hoisting Confusion with <code>const</code> and <code>let</code></h3>

<p>I thought I understood hoisting from ES5, but <code>const</code> and <code>let</code> behave differently. This caused me some confusion early on.</p>

<div class="code-block" data-title="Hoisting Differences">
<pre><code class="language-javascript">// This works (var is hoisted)
console.log(x); // undefined
var x = 5;

// This doesn't work (let is not hoisted)
console.log(y); // ReferenceError: Cannot access 'y' before initialization
let y = 5;</code></pre>
</div>

<p><strong>Lesson learned</strong>: Always declare your variables at the top of their scope, regardless of whether you're using <code>var</code>, <code>let</code>, or <code>const</code>.</p>

<h3>2. Object Property Shorthand Confusion</h3>

<p>I was excited about object property shorthand, but I overused it in places where it made my code less readable.</p>

<div class="code-block" data-title="Object Shorthand Best Practices">
<pre><code class="language-javascript">// Good use
const name = 'John';
const age = 30;
const user = { name, age };

// Bad use (less readable)
const user = { name: 'John', age: 30, role: 'admin', isActive: true, lastLogin: new Date() };
// vs
const user = { 
    name: 'John', 
    age: 30, 
    role: 'admin', 
    isActive: true, 
    lastLogin: new Date() 
};</code></pre>
</div>

<p><strong>Rule of thumb</strong>: Use shorthand when you have 2-3 properties, use regular syntax for more complex objects.</p>

<h3>3. Default Parameter Gotchas</h3>

<p>Default parameters seem simple, but they can have unexpected behavior with objects and arrays.</p>

<div class="code-block" data-title="Default Parameter Issues">
<pre><code class="language-javascript">// This doesn't work as expected
function createUser(user = { name: 'Anonymous' }) {
    user.name = 'John'; // This modifies the default object!
    return user;
}

const user1 = createUser(); // { name: 'John' }
const user2 = createUser(); // { name: 'John' } - Same object!

// Better approach
function createUser(user = {}) {
    return {
        name: 'Anonymous',
        ...user
    };
}</code></pre>
</div>

<h2>The Real-World Impact on My Projects</h2>

<p>After implementing these ES6+ features in my projects, I noticed several improvements:</p>

<ol>
<li><strong>Code Readability</strong>: My team could understand my code much faster</li>
<li><strong>Fewer Bugs</strong>: Destructuring and default parameters reduced undefined errors</li>
<li><strong>Better Performance</strong>: Arrow functions and template literals are slightly more efficient</li>
<li><strong>Easier Maintenance</strong>: Modern syntax made refactoring much simpler</li>
</ol>

<h2>What I Wish I Knew Earlier</h2>

<ol>
<li><strong>Start Small</strong>: Don't try to refactor everything at once. Pick one feature and master it before moving to the next.</li>
<li><strong>Use ESLint</strong>: Configure ESLint with ES6+ rules. It will catch many common mistakes and teach you best practices.</li>
<li><strong>Practice with Real Projects</strong>: Don't just read about these features - implement them in your actual code.</li>
<li><strong>Understand the Why</strong>: Don't just learn the syntax; understand when and why to use each feature.</li>
</ol>

<h2>Next Steps for Your Journey</h2>

<p>If you're just starting with ES6+, here's my recommended learning path:</p>

<ol>
<li><strong>Week 1</strong>: Master <code>const</code>, <code>let</code>, and arrow functions</li>
<li><strong>Week 2</strong>: Learn template literals and destructuring</li>
<li><strong>Week 3</strong>: Practice with spread/rest operators</li>
<li><strong>Week 4</strong>: Dive into async/await and Promises</li>
</ol>

<h2>Conclusion</h2>

<p>Learning modern JavaScript wasn't just about learning new syntax - it was about writing better, more maintainable code. The ES6+ features I've covered here have become essential tools in my development toolkit.</p>

<p>Remember, the goal isn't to use every new feature everywhere. It's about choosing the right tool for the job. Sometimes the old ES5 way is still the best approach, and that's perfectly fine.</p>

<p>What ES6+ features are you most excited to learn? Have you encountered any of the pitfalls I mentioned? I'd love to hear about your experiences in the comments below.</p>

<hr>

<p><em>This post is based on my real experiences learning and implementing ES6+ features in production applications. The examples and code snippets come from actual projects I've worked on, and the lessons learned are from real debugging sessions and code reviews.</em></p>`;

    // Insert the content at cursor position
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        range.deleteContents();
        
        // Create a temporary div to parse the HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = sampleContent;
        
        // Insert the cleaned content
        const fragment = document.createDocumentFragment();
        while (tempDiv.firstChild) {
            fragment.appendChild(tempDiv.firstChild);
        }
        range.insertNode(fragment);
        
        // Update textarea
        const contentTextarea = document.getElementById('content');
        if (contentTextarea) {
            contentTextarea.value = editor.innerHTML;
        }
        
        // Apply syntax highlighting
        applySyntaxHighlighting();
    }
}

// Clean and process pasted HTML content
function cleanPastedHtml(html) {
    // Remove potentially harmful tags and attributes
    const allowedTags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 'u', 'code', 'pre', 'div', 'span', 'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'br', 'hr'];
    const allowedAttributes = ['class', 'id', 'href', 'src', 'alt', 'title', 'data-title', 'data-language'];
    
    // Create a temporary div to parse and clean the HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    
    // Function to clean a node recursively
    function cleanNode(node) {
        if (node.nodeType === Node.ELEMENT_NODE) {
            const tagName = node.tagName.toLowerCase();
            
            // Remove disallowed tags
            if (!allowedTags.includes(tagName)) {
                // Replace with text content
                const textNode = document.createTextNode(node.textContent);
                node.parentNode.replaceChild(textNode, node);
                return;
            }
            
            // Clean attributes
            const attributes = Array.from(node.attributes);
            attributes.forEach(attr => {
                if (!allowedAttributes.includes(attr.name)) {
                    node.removeAttribute(attr.name);
                }
            });
            
            // Special handling for code blocks
            if (tagName === 'pre') {
                // Ensure it has the enhanced-code class
                if (!node.classList.contains('enhanced-code')) {
                    node.classList.add('enhanced-code');
                }
                
                // Add data-title if it's a code block
                if (!node.hasAttribute('data-title')) {
                    node.setAttribute('data-title', 'Code Example');
                }
            }
            
            // Special handling for inline code
            if (tagName === 'code' && node.parentElement && node.parentElement.tagName !== 'PRE') {
                if (!node.classList.contains('enhanced-inline-code')) {
                    node.classList.add('enhanced-inline-code');
                }
            }
            
            // Recursively clean child nodes
            const children = Array.from(node.childNodes);
            children.forEach(child => cleanNode(child));
        }
    }
    
    // Clean all nodes
    const allNodes = Array.from(tempDiv.childNodes);
    allNodes.forEach(node => cleanNode(node));
    
    return tempDiv.innerHTML;
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
    const language = prompt('Enter programming language (e.g., javascript, html, css, php, python):', 'javascript') || 'javascript';
    const title = prompt('Enter code block title (optional):', '') || '';
    
    const codeBlock = `
<div class="code-block" data-language="${language.toLowerCase()}" ${title ? `data-title="${title}"` : ''}>
<pre><code>
// Your ${language} code here
${language === 'javascript' ? 'console.log("Hello, World!");' : 
  language === 'html' ? '<div>Hello World</div>' :
  language === 'css' ? 'body { color: #333; }' :
  language === 'php' ? '<?php echo "Hello World"; ?>' :
  language === 'python' ? 'print("Hello, World!")' :
  language === 'sql' ? 'SELECT * FROM users;' :
  '// Add your code here'}
</code></pre>
</div>
`;
    
    insertHTML(codeBlock);
    
    // Add copy button and apply highlighting after insertion
    setTimeout(() => {
        const newCodeBlock = document.querySelector('.code-block:last-child');
        if (newCodeBlock) {
            addCopyButton(newCodeBlock);
            applySyntaxHighlighting();
        }
    }, 100);
}

// Edit code block title
function editCodeBlockTitle(codeBlockElement) {
    const currentTitle = codeBlockElement.getAttribute('data-title') || '';
    const newTitle = prompt('Enter code block title:', currentTitle);
    
    if (newTitle !== null) { // User didn't cancel
        if (newTitle.trim() === '') {
            // Remove title if empty
            codeBlockElement.removeAttribute('data-title');
        } else {
            // Set new title
            codeBlockElement.setAttribute('data-title', newTitle.trim());
        }
        
        // Update the display
        updateCodeBlockTitle(codeBlockElement);
        updateContent();
    }
}

// Update code block title display
function updateCodeBlockTitle(codeBlockElement) {
    const title = codeBlockElement.getAttribute('data-title');
    let titleElement = codeBlockElement.querySelector('.code-title');
    
    if (title) {
        if (!titleElement) {
            titleElement = document.createElement('div');
            titleElement.className = 'code-title';
            titleElement.style.cssText = 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 12px; border-radius: 6px 6px 0 0; font-size: 14px; font-weight: 600; margin-bottom: 0; border-bottom: 1px solid #e9ecef;';
            codeBlockElement.insertBefore(titleElement, codeBlockElement.firstChild);
        }
        titleElement.textContent = title;
    } else if (titleElement) {
        titleElement.remove();
    }
}

// Edit selected code block title
function editSelectedCodeBlockTitle() {
    const selection = window.getSelection();
    if (selection.rangeCount) {
        const range = selection.getRangeAt(0);
        let codeBlockElement = range.commonAncestorContainer;
        
        // Find the closest code block element
        while (codeBlockElement && !codeBlockElement.classList.contains('code-block')) {
            codeBlockElement = codeBlockElement.parentElement;
        }
        
        if (codeBlockElement && codeBlockElement.classList.contains('code-block')) {
            editCodeBlockTitle(codeBlockElement);
        } else {
            // If no code block is selected, show a message
            alert('Please place your cursor inside a code block to edit its title.');
        }
    } else {
        alert('Please place your cursor inside a code block to edit its title.');
    }
}

// Insert inline code
function insertInlineCode() {
    const selection = window.getSelection();
    if (selection.rangeCount) {
        const range = selection.getRangeAt(0);
        const selectedText = range.toString();
        
        if (selectedText) {
            // Wrap selected text in inline code
        const codeElement = document.createElement('code');
            codeElement.className = 'inline-code';
            codeElement.textContent = selectedText;
        range.deleteContents();
        range.insertNode(codeElement);
        } else {
            // Insert inline code placeholder
            const codeElement = document.createElement('code');
            codeElement.className = 'inline-code';
            codeElement.textContent = 'inline code';
            range.insertNode(codeElement);
        }
        
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
    // Create a modal for image insertion options
    const modalHTML = `
        <div class="modal fade" id="imageInsertModal" tabindex="-1" aria-labelledby="imageInsertModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageInsertModalLabel">
                            <i class="fas fa-image me-2"></i>Insert Image
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Choose Image Source:</label>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="showImageUpload()">
                                    <i class="fas fa-upload me-2"></i>Upload from Computer
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="showImageURL()">
                                    <i class="fas fa-link me-2"></i>Enter Image URL
                                </button>
                            </div>
                        </div>
                        
                        <!-- Image Upload Section -->
                        <div id="imageUploadSection" style="display: none;">
                            <div class="mb-3">
                                <label for="imageFile" class="form-label">Select Image File:</label>
                                <input type="file" class="form-control" id="imageFile" accept="image/*" onchange="previewImage(this)">
                                <div class="form-text">Supported formats: JPG, PNG, GIF, WebP (Max size: 5MB)</div>
                            </div>
                            <div id="imagePreview" class="text-center mb-3" style="display: none;">
                                <img id="previewImg" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                            <div class="mb-3">
                                <label for="imageAltText" class="form-label">Alt Text:</label>
                                <input type="text" class="form-control" id="imageAltText" placeholder="Describe the image for accessibility">
                            </div>
                            <div class="mb-3">
                                <label for="imageCaption" class="form-label">Caption (optional):</label>
                                <input type="text" class="form-control" id="imageCaption" placeholder="Image caption">
                            </div>
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" onclick="uploadAndInsertImage()">
                                    <i class="fas fa-upload me-2"></i>Upload & Insert
                                </button>
                            </div>
                        </div>
                        
                        <!-- Image URL Section -->
                        <div id="imageURLSection" style="display: none;">
                            <div class="mb-3">
                                <label for="imageUrl" class="form-label">Image URL:</label>
                                <input type="url" class="form-control" id="imageUrl" placeholder="https://example.com/image.jpg">
                            </div>
                            <div class="mb-3">
                                <label for="urlAltText" class="form-label">Alt Text:</label>
                                <input type="text" class="form-control" id="urlAltText" placeholder="Describe the image for accessibility">
                            </div>
                            <div class="mb-3">
                                <label for="urlCaption" class="form-label">Caption (optional):</label>
                                <input type="text" class="form-control" id="urlCaption" placeholder="Image caption">
                            </div>
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" onclick="insertImageFromURL()">
                                    <i class="fas fa-plus me-2"></i>Insert Image
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if it exists
    const existingModal = document.getElementById('imageInsertModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('imageInsertModal'));
    modal.show();
}

// Show image upload section
function showImageUpload() {
    document.getElementById('imageUploadSection').style.display = 'block';
    document.getElementById('imageURLSection').style.display = 'none';
}

// Show image URL section
function showImageURL() {
    document.getElementById('imageUploadSection').style.display = 'none';
    document.getElementById('imageURLSection').style.display = 'block';
}

// Preview selected image
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            
            // Auto-fill alt text with filename
            const altTextInput = document.getElementById('imageAltText');
            if (altTextInput && !altTextInput.value) {
                altTextInput.value = file.name.replace(/\.[^/.]+$/, ""); // Remove file extension
            }
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

// Upload and insert image
function uploadAndInsertImage() {
    const fileInput = document.getElementById('imageFile');
    const altText = document.getElementById('imageAltText').value;
    const caption = document.getElementById('imageCaption').value;
    
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Please select an image file');
        return;
    }
    
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('image', file);
    formData.append('alt_text', altText);
    formData.append('caption', caption);
    
    // Show loading state
    const uploadBtn = document.querySelector('#imageUploadSection .btn-primary');
    const originalText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    uploadBtn.disabled = true;
    
    // Upload the image
            fetch('upload_image', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get response text first to see what we're actually getting
        return response.text();
    })
    .then(text => {
        // Check if response is empty
        if (!text || text.trim() === '') {
            throw new Error('Empty response from server');
        }
        
        // Try to parse as JSON
        try {
            const data = JSON.parse(text);
            return data;
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Raw response:', text);
            throw new Error('Invalid response from server: ' + text.substring(0, 100));
        }
    })
    .then(data => {
        if (data.success) {
            // Insert the uploaded image into the editor
            let imageHTML = `<img src="${data.file_path}" alt="${altText || ''}" class="img-fluid">`;
            
            // Add caption if provided
            if (caption) {
                imageHTML = `
                    <figure class="figure">
                        <img src="${data.file_path}" alt="${altText || ''}" class="figure-img img-fluid rounded">
                        <figcaption class="figure-caption">${caption}</figcaption>
                    </figure>
                `;
            }
            
            insertHTML(imageHTML);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('imageInsertModal'));
            modal.hide();
            
            // Reset form
            fileInput.value = '';
            document.getElementById('imageAltText').value = '';
            document.getElementById('imageCaption').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            
            // Show success message
            showAlert('Image uploaded and inserted successfully!', 'success');
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Error uploading image: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
    });
}

// Insert image from URL
function insertImageFromURL() {
    const imageUrl = document.getElementById('imageUrl').value.trim();
    const altText = document.getElementById('urlAltText').value.trim();
    const caption = document.getElementById('urlCaption').value.trim();
    
    if (!imageUrl) {
        alert('Please enter an image URL');
        return;
    }
    
    // Validate URL
    try {
        new URL(imageUrl);
    } catch (e) {
        alert('Please enter a valid URL');
        return;
    }
    
    // Insert the image into the editor
    let imageHTML = `<img src="${imageUrl}" alt="${altText || ''}" class="img-fluid">`;
    
    // Add caption if provided
    if (caption) {
        imageHTML = `
            <figure class="figure">
                <img src="${imageUrl}" alt="${altText || ''}" class="figure-img img-fluid rounded">
                <figcaption class="figure-caption">${caption}</figcaption>
            </figure>
        `;
    }
    
    insertHTML(imageHTML);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('imageInsertModal'));
    modal.hide();
    
    // Reset form
    document.getElementById('imageUrl').value = '';
    document.getElementById('urlAltText').value = '';
    document.getElementById('urlCaption').value = '';
    
    // Show success message
    showAlert('Image inserted successfully!', 'success');
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHTML);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 3000);
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

// Apply syntax highlighting to code blocks
function applySyntaxHighlighting() {
    const codeBlocks = document.querySelectorAll('.code-block');
    codeBlocks.forEach(block => {
        const code = block.querySelector('code');
        if (code) {
            const language = block.getAttribute('data-language');
            const text = code.textContent;
            
            // Simple syntax highlighting based on language
            let highlightedText = text;
            
            if (language === 'javascript' || language === 'js') {
                highlightedText = text
                    .replace(/\b(const|let|var|function|return|if|else|for|while|class|import|export|default)\b/g, '<span class="keyword">$1</span>')
                    .replace(/(["'`])(.*?)\1/g, '<span class="string">$1$2$1</span>')
                    .replace(/\b(\d+)\b/g, '<span class="number">$1</span>')
                    .replace(/\b(console|log|alert|prompt)\b/g, '<span class="function">$1</span>')
                    .replace(/(\/\/.*$)/gm, '<span class="comment">$1</span>');
            } else if (language === 'html') {
                highlightedText = text
                    .replace(/(&lt;\/?)([a-zA-Z][a-zA-Z0-9]*)/g, '$1<span class="tag">$2</span>')
                    .replace(/(["'])(.*?)\1/g, '<span class="string">$1$2$1</span>')
                    .replace(/(&lt;!--.*?--&gt;)/g, '<span class="comment">$1</span>');
            } else if (language === 'css') {
                highlightedText = text
                    .replace(/([a-zA-Z-]+)(?=\s*:)/g, '<span class="property">$1</span>')
                    .replace(/(["'])(.*?)\1/g, '<span class="string">$1$2$1</span>')
                    .replace(/(\/\*.*?\*\/)/gs, '<span class="comment">$1</span>')
                    .replace(/(\d+)/g, '<span class="number">$1</span>');
            } else if (language === 'php') {
                highlightedText = text
                    .replace(/\b(echo|print|function|class|public|private|protected|static|const|return|if|else|foreach|while|for|switch|case|break|continue)\b/g, '<span class="keyword">$1</span>')
                    .replace(/(["'])(.*?)\1/g, '<span class="string">$1$2$1</span>')
                    .replace(/\b(\d+)\b/g, '<span class="number">$1</span>')
                    .replace(/(\/\/.*$)/gm, '<span class="comment">$1</span>')
                    .replace(/(\/\*.*?\*\/)/gs, '<span class="comment">$1</span>');
            }
            
            code.innerHTML = highlightedText;
        }
        
        // Add copy button if not already present
        if (!block.querySelector('.copy-btn')) {
            addCopyButton(block);
        }
        
        // Update code block title display
        updateCodeBlockTitle(block);
    });
}

// Add copy button to code blocks
function addCopyButton(codeBlock) {
    const copyBtn = document.createElement('button');
    copyBtn.className = 'copy-btn';
    copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
    copyBtn.title = 'Copy code';
    copyBtn.onclick = function() {
        const code = codeBlock.querySelector('code');
        if (code) {
            navigator.clipboard.writeText(code.textContent).then(() => {
                // Show success feedback
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                copyBtn.style.background = '#38a169';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                    copyBtn.style.background = '#4299e1';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = code.textContent;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                copyBtn.style.background = '#38a169';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                    copyBtn.style.background = '#4299e1';
                }, 2000);
            });
        }
    };
    
    codeBlock.appendChild(copyBtn);
}

// Enhanced media insertion
function insertMedia() {
    const modal = new bootstrap.Modal(document.getElementById('mediaPickerModal'));
    modal.show();
    loadMediaFiles();
}

// Add right-click context menu for code blocks
function addCodeBlockContextMenu() {
    const editor = document.getElementById('editor');
    if (!editor) return;
    
    editor.addEventListener('contextmenu', function(e) {
        const codeBlock = e.target.closest('.code-block');
        if (codeBlock) {
            e.preventDefault();
            
            // Create context menu
            const contextMenu = document.createElement('div');
            contextMenu.className = 'context-menu';
            contextMenu.style.cssText = `
                position: fixed;
                top: ${e.pageY}px;
                left: ${e.pageX}px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 1000;
                min-width: 150px;
                padding: 4px 0;
            `;
            
            const menuItems = [
                { text: 'Edit Title', icon: 'fas fa-heading', action: () => editCodeBlockTitle(codeBlock) },
                { text: 'Copy Code', icon: 'fas fa-copy', action: () => copyCodeBlock(codeBlock) },
                { text: 'Remove Title', icon: 'fas fa-times', action: () => removeCodeBlockTitle(codeBlock) }
            ];
            
            menuItems.forEach(item => {
                const menuItem = document.createElement('div');
                menuItem.style.cssText = `
                    padding: 8px 16px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    transition: background-color 0.2s;
                `;
                menuItem.innerHTML = `<i class="${item.icon}"></i> ${item.text}`;
                menuItem.addEventListener('click', () => {
                    item.action();
                    document.body.removeChild(contextMenu);
                });
                menuItem.addEventListener('mouseenter', () => {
                    menuItem.style.backgroundColor = '#f8f9fa';
                });
                menuItem.addEventListener('mouseleave', () => {
                    menuItem.style.backgroundColor = 'transparent';
                });
                contextMenu.appendChild(menuItem);
            });
            
            document.body.appendChild(contextMenu);
            
            // Remove context menu when clicking elsewhere
            setTimeout(() => {
                document.addEventListener('click', function removeMenu() {
                    document.body.removeChild(contextMenu);
                    document.removeEventListener('click', removeMenu);
                }, 0);
            }, 0);
        }
    });
}

// Copy code block content
function copyCodeBlock(codeBlock) {
    const code = codeBlock.querySelector('code');
    if (code) {
        navigator.clipboard.writeText(code.textContent).then(() => {
            // Show success feedback
            const originalText = codeBlock.querySelector('.copy-btn')?.innerHTML || '<i class="fas fa-copy"></i>';
            const copyBtn = codeBlock.querySelector('.copy-btn');
            if (copyBtn) {
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                copyBtn.style.background = '#38a169';
                setTimeout(() => {
                    copyBtn.innerHTML = originalText;
                    copyBtn.style.background = '#4299e1';
                }, 2000);
            }
        });
    }
}

// Remove code block title
function removeCodeBlockTitle(codeBlock) {
    codeBlock.removeAttribute('data-title');
    updateCodeBlockTitle(codeBlock);
    updateContent();
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
        form.action = 'posts';
        
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
            fetch('media?action=get_media')
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

// Course module selection handlers
document.addEventListener('DOMContentLoaded', function() {
    // Handle course module selection for add form
    const newCourseSelect = document.getElementById('new_course_module_id');
    const newLessonOrderContainer = document.getElementById('new-lesson-order-container');
    
    if (newCourseSelect && newLessonOrderContainer) {
        newCourseSelect.addEventListener('change', function() {
            if (this.value) {
                newLessonOrderContainer.style.display = 'block';
            } else {
                newLessonOrderContainer.style.display = 'none';
            }
        });
        
        // Show/hide on page load
        if (newCourseSelect.value) {
            newLessonOrderContainer.style.display = 'block';
        }
    }
    
    // Handle course module selection for edit form
    const editCourseSelect = document.getElementById('course_module_id');
    const editLessonOrderContainer = document.getElementById('lesson-order-container');
    
    if (editCourseSelect && editLessonOrderContainer) {
        editCourseSelect.addEventListener('change', function() {
            if (this.value) {
                editLessonOrderContainer.style.display = 'block';
            } else {
                editLessonOrderContainer.style.display = 'none';
            }
        });
        
        // Show/hide on page load
        if (editCourseSelect.value) {
            editLessonOrderContainer.style.display = 'block';
        }
    }
});

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
                <a href="media" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i>Upload New Media
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php include '../includes/modals.php'; ?>
<?php include '../includes/scripts.php'; ?>