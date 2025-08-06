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
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allPosts)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
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
                            <div class="editor-toolbar mb-2">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCodeBlock()">
                                        <i class="fas fa-code"></i> Code Block
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertInlineCode()">
                                        <i class="fas fa-code"></i> Inline Code
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink()">
                                        <i class="fas fa-link"></i> Link
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertImage()">
                                        <i class="fas fa-image"></i> Image
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
                            <textarea class="form-control" id="content" name="content" rows="20" required></textarea>
                            <div class="form-text">
                                Use the toolbar buttons to insert code blocks, links, and images. 
                                Code blocks will be automatically syntax highlighted.
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
                            <div class="editor-toolbar mb-2">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCodeBlock()">
                                        <i class="fas fa-code"></i> Code Block
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertInlineCode()">
                                        <i class="fas fa-code"></i> Inline Code
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink()">
                                        <i class="fas fa-link"></i> Link
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertImage()">
                                        <i class="fas fa-image"></i> Image
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
                            <textarea class="form-control" id="content" name="content" rows="20" required><?php echo htmlspecialchars($postData['content']); ?></textarea>
                            <div class="form-text">
                                Use the toolbar buttons to insert code blocks, links, and images. 
                                Code blocks will be automatically syntax highlighted.
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
// Initialize CodeMirror for content editor
let editor;
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    const contentTextarea = document.getElementById('content');
    console.log('Content textarea found:', contentTextarea);
    
    if (contentTextarea) {
        try {
            editor = CodeMirror.fromTextArea(contentTextarea, {
                mode: 'htmlmixed',
                theme: 'default',
                lineNumbers: true,
                lineWrapping: true,
                indentUnit: 4,
                tabSize: 4,
                extraKeys: {
                    "Ctrl-Space": "autocomplete",
                    "Ctrl-/": "toggleComment"
                },
                autoCloseTags: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                matchTags: true,
                foldGutter: true,
                gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
            });
            
            // Set initial size
            editor.setSize("100%", "400px");
            console.log('CodeMirror editor initialized successfully');
            
            // Ensure the textarea is updated when editor content changes
            editor.on('change', function() {
                editor.save();
            });
            
        } catch (error) {
            console.error('Error initializing CodeMirror:', error);
        }
    } else {
        console.log('Content textarea not found');
    }
});

// Insert code block
function insertCodeBlock() {
    const language = document.getElementById('codeLanguage') ? 
                    document.getElementById('codeLanguage').value : 'javascript';
    
    const codeBlock = `\n<pre><code class="language-${language}">
// Your ${language} code here
function example() {
    console.log("Hello, World!");
}
</code></pre>\n`;
    
    if (editor) {
        const cursor = editor.getCursor();
        editor.replaceRange(codeBlock, cursor);
        editor.focus();
    } else {
        // Fallback for regular textarea
        const textarea = document.getElementById('content');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + codeBlock + text.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + codeBlock.length, start + codeBlock.length);
    }
}

// Insert inline code
function insertInlineCode() {
    const code = prompt("Enter inline code:", "example");
    if (code) {
        const inlineCode = `<code class="language-javascript">${code}</code>`;
        
        if (editor) {
            const cursor = editor.getCursor();
            editor.replaceRange(inlineCode, cursor);
            editor.focus();
        } else {
            // Fallback for regular textarea
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + inlineCode + text.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + inlineCode.length, start + inlineCode.length);
        }
    }
}

// Insert link
function insertLink() {
    const url = prompt("Enter URL:", "https://");
    const text = prompt("Enter link text:", "Link");
    
    if (url && text) {
        const link = `<a href="${url}" target="_blank">${text}</a>`;
        
        if (editor) {
            const cursor = editor.getCursor();
            editor.replaceRange(link, cursor);
            editor.focus();
        } else {
            // Fallback for regular textarea
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + link + text.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + link.length, start + link.length);
        }
    }
}

// Insert image
function insertImage() {
    // Redirect to media picker instead
    openMediaPicker();
}

// Change code theme
function changeCodeTheme() {
    if (!editor) return;
    
    const theme = document.getElementById('codeTheme').value;
    editor.setOption('theme', theme);
}

// Change code language
function changeCodeLanguage() {
    if (!editor) return;
    
    const language = document.getElementById('codeLanguage').value;
    // Update the editor mode based on language
    const modeMap = {
        'javascript': 'javascript',
        'python': 'python',
        'php': 'php',
        'html': 'htmlmixed',
        'css': 'css',
        'sql': 'sql',
        'bash': 'text'
    };
    
    const mode = modeMap[language] || 'htmlmixed';
    editor.setOption('mode', mode);
}

// Update textarea before form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Always save editor content to textarea before submission
            if (editor) {
                editor.save();
                console.log('Editor content saved:', document.getElementById('content').value);
            } else {
                console.log('Textarea content:', document.getElementById('content').value);
            }
            
            // Ensure the textarea has content for validation
            const textarea = document.getElementById('content');
            if (textarea && textarea.value.trim() === '') {
                e.preventDefault();
                alert('Please enter some content for your post.');
                if (editor) {
                    editor.focus();
                } else {
                    textarea.focus();
                }
                return false;
            }
        });
    }
});

// Delete post function
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

// Function to add a tag to the input field
function addTagToInput(tag) {
    const tagsInput = document.getElementById('tags');
    if (tagsInput) {
        const currentTags = tagsInput.value.split(',').map(t => t.trim());
        if (!currentTags.includes(tag)) {
            currentTags.push(tag);
            tagsInput.value = currentTags.join(', ');
        }
        tagsInput.focus();
    }
}

// Media picker functions
function openMediaPicker() {
    loadMediaFiles();
    const modal = new bootstrap.Modal(document.getElementById('mediaPickerModal'));
    modal.show();
}

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
    const imageUrl = filePath;
    const imageMarkdown = `![${altText || fileName}](${imageUrl})`;
    
    if (editor) {
        const cursor = editor.getCursor();
        editor.replaceRange(imageMarkdown, cursor);
        editor.focus();
    } else {
        const textarea = document.getElementById('content');
        const cursorPos = textarea.selectionStart;
        const textBefore = textarea.value.substring(0, cursorPos);
        const textAfter = textarea.value.substring(cursorPos);
        textarea.value = textBefore + imageMarkdown + textAfter;
        textarea.focus();
    }
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('mediaPickerModal'));
    modal.hide();
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