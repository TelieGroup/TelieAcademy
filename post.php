<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/Comment.php';
require_once 'includes/Reply.php';
require_once 'includes/User.php';
require_once 'includes/Tag.php';
require_once 'includes/View.php';
require_once 'includes/Vote.php';
require_once 'includes/Course.php';

$post = new Post();
$comment = new Comment();
$reply = new Reply();
$user = new User();
$tag = new Tag();
$view = new View();
$vote = new Vote();
$course = new Course();

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

if (empty($slug)) {
    header('Location: index');
    exit;
}

$postData = $post->getPostBySlug($slug, $isPremium);

// Get course context if this post is part of a course
$courseContext = null;
$courseModuleInfo = null;
$nextLesson = null;
$prevLesson = null;
$userProgress = null;
$lessonMaterials = [];

if ($postData && !empty($postData['course_module_id'])) {
    // Get module information
    $courseModuleInfo = $course->getModuleById($postData['course_module_id']);
    
    if ($courseModuleInfo) {
        // Get course information
        $courseContext = $course->getCourseById($courseModuleInfo['course_id']);
        
        // Get all posts in this module for navigation
        $modulePosts = $course->getPostsByModule($postData['course_module_id'], true);
        
        // Find current post position and get next/previous
        foreach ($modulePosts as $index => $modulePost) {
            if ($modulePost['id'] == $postData['id']) {
                $nextLesson = isset($modulePosts[$index + 1]) ? $modulePosts[$index + 1] : null;
                $prevLesson = isset($modulePosts[$index - 1]) ? $modulePosts[$index - 1] : null;
                break;
            }
        }
        
        // Check enrollment if user is logged in
        $courseEnrollment = null;
        if ($isLoggedIn) {
            $currentUser = $user->getCurrentUser();
            $courseEnrollment = $course->getUserCourseEnrollment($currentUser['id'], $courseContext['id']);
            
            // If not enrolled, redirect to course page with enrollment message
            if (!$courseEnrollment) {
                header('Location: course-view?course=' . $courseContext['slug'] . '&error=enrollment_required');
                exit;
            }
            
            $progressData = $course->getUserCourseProgress($currentUser['id'], $courseContext['id']);
            $userProgress = isset($progressData[$postData['id']]) ? $progressData[$postData['id']] : null;
            
            // Get materials related to this lesson
            $lessonMaterials = $course->getMaterialsByLesson($postData['id'], $currentUser['id']);
        } else {
            // If not logged in, redirect to course page
            header('Location: course-view?course=' . $courseContext['slug'] . '&error=login_required');
            exit;
        }
    }
}

// Enhance post content HTML (images: responsive, lazy-loading, enhanced styling)
function enhancePostContentHtml($html) {
    if (!is_string($html) || $html === '') {
        return $html;
    }
    $internalErrors = libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    // Wrap content to preserve fragment
    $wrapped = '<div id="__content_wrapper__">' . $html . '</div>';
    $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $xpath = new DOMXPath($dom);
    
    // Enhance images
    $images = $xpath->query('//img');
    foreach ($images as $img) {
        // Create image container
        $container = $dom->createElement('div');
        $container->setAttribute('class', 'image-container');
        
        // Ensure responsive class
        $class = $img->getAttribute('class');
        if (strpos(' ' . $class . ' ', ' img-fluid ') === false) {
            $class = trim($class . ' img-fluid');
            $img->setAttribute('class', $class);
        }
        // Add lazy loading and async decoding
        if (!$img->hasAttribute('loading')) {
            $img->setAttribute('loading', 'lazy');
        }
        if (!$img->hasAttribute('decoding')) {
            $img->setAttribute('decoding', 'async');
        }
        // Ensure alt exists
        if (!$img->hasAttribute('alt')) {
            $img->setAttribute('alt', '');
        }
        // Add referrer policy for security
        if (!$img->hasAttribute('referrerpolicy')) {
            $img->setAttribute('referrerpolicy', 'no-referrer');
        }
        
        // Wrap image in container
        $img->parentNode->replaceChild($container, $img);
        $container->appendChild($img);
    }
    
    // Enhance code blocks - handle both traditional <pre> and new admin-created div structures
    $codeBlocks = $xpath->query('//pre');
    foreach ($codeBlocks as $pre) {
        $class = $pre->getAttribute('class');
        if (strpos(' ' . $class . ' ', ' enhanced-code ') === false) {
            $class = trim($class . ' enhanced-code');
            $pre->setAttribute('class', $class);
        }
        
        // Check if this code block is inside a div with custom title
        $parentDiv = $pre->parentNode;
        $customTitle = '';
        
        // Look for parent div with data-title attribute (from admin editor)
        while ($parentDiv && $parentDiv->nodeName !== 'div') {
            $parentDiv = $parentDiv->parentNode;
        }
        
        if ($parentDiv && $parentDiv->nodeName === 'div' && $parentDiv->hasAttribute('data-title')) {
            $customTitle = $parentDiv->getAttribute('data-title');
        }
        
        // If no custom title, check for preceding heading or comment
        if (empty($customTitle)) {
            // Check if there's a preceding heading (h1-h6) that could serve as a title
            $prevSibling = $pre->previousSibling;
            while ($prevSibling && $prevSibling->nodeType === XML_TEXT_NODE) {
                $prevSibling = $prevSibling->previousSibling;
            }
            
            if ($prevSibling && $prevSibling->nodeName && in_array($prevSibling->nodeName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                $customTitle = trim($prevSibling->textContent);
            } else {
                // Check if there's a preceding comment that could serve as a title
                $prevSibling = $pre->previousSibling;
                while ($prevSibling && $prevSibling->nodeType === XML_TEXT_NODE) {
                    $prevSibling = $prevSibling->previousSibling;
                }
                
                if ($prevSibling && $prevSibling->nodeName === 'p') {
                    $text = trim($prevSibling->textContent);
                    // Check if this paragraph looks like a title (short, ends with colon, etc.)
                    if (strlen($text) < 100 && (strpos($text, ':') !== false || strpos($text, 'Example') !== false || strpos($text, 'Code') !== false)) {
                        $customTitle = $text;
                    }
                }
            }
        }
        
        // If still no title found, use a default based on content
        if (empty($customTitle)) {
            $codeElements = $pre->getElementsByTagName('code');
            if ($codeElements->length > 0) {
                $code = $codeElements->item(0);
                $codeText = trim($code->textContent);
                if (strpos($codeText, '<?php') !== false) {
                    $customTitle = 'PHP Code';
                } elseif (strpos($codeText, '<html') !== false || strpos($codeText, '<div') !== false) {
                    $customTitle = 'HTML Code';
                } elseif (strpos($codeText, 'function') !== false || strpos($codeText, 'console.log') !== false) {
                    $customTitle = 'JavaScript Code';
                } elseif (strpos($codeText, 'body {') !== false || strpos($codeText, 'color:') !== false) {
                    $customTitle = 'CSS Code';
                } elseif (strpos($codeText, 'SELECT') !== false || strpos($codeText, 'INSERT') !== false) {
                    $customTitle = 'SQL Code';
                } else {
                    $customTitle = 'Code Example';
                }
            } else {
                $customTitle = 'Code Example';
            }
        }
        
        $pre->setAttribute('data-title', $customTitle);
    }
    
    // Also handle new admin-created code block structures (div.code-block with data-title)
    $adminCodeBlocks = $xpath->query('//div[contains(@class, "code-block")]');
    foreach ($adminCodeBlocks as $codeBlock) {
        if ($codeBlock->hasAttribute('data-title')) {
            $customTitle = $codeBlock->getAttribute('data-title');
            // Find the pre element inside and set its data-title
            $preElements = $codeBlock->getElementsByTagName('pre');
            if ($preElements->length > 0) {
                $preElement = $preElements->item(0);
                $preElement->setAttribute('data-title', $customTitle);
                // Also ensure it has the enhanced-code class
                $class = $preElement->getAttribute('class');
                if (strpos(' ' . $class . ' ', ' enhanced-code ') === false) {
                    $class = trim($class . ' enhanced-code');
                    $preElement->setAttribute('class', $class);
                }
            }
        }
    }
    
    // Enhance inline code
    $inlineCodes = $xpath->query('//code[not(parent::pre)]');
    foreach ($inlineCodes as $code) {
        $class = $code->getAttribute('class');
        if (strpos(' ' . $class . ' ', ' enhanced-inline-code ') === false) {
            $class = trim($class . ' enhanced-inline-code');
            $code->setAttribute('class', $class);
        }
    }
    
    // Enhance tables
    $tables = $xpath->query('//table');
    foreach ($tables as $table) {
        $class = $table->getAttribute('class');
        if (strpos(' ' . $class . ' ', ' enhanced-table ') === false) {
            $class = trim($class . ' enhanced-table');
            $table->setAttribute('class', $class);
        }
    }
    
    // Enhance blockquotes
    $blockquotes = $xpath->query('//blockquote');
    foreach ($blockquotes as $blockquote) {
        $class = $blockquote->getAttribute('class');
        if (strpos(' ' . $class . ' ', ' enhanced-blockquote ') === false) {
            $class = trim($class . ' enhanced-blockquote');
            $blockquote->setAttribute('class', $class);
        }
    }

    // Extract inner HTML of wrapper
    $wrapper = $dom->getElementById('__content_wrapper__');
    $output = '';
    if ($wrapper) {
        foreach ($wrapper->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }
    }
    libxml_clear_errors();
    libxml_use_internal_errors($internalErrors);
    return $output;
}

// Prepare enhanced content for rendering
$renderedContent = enhancePostContentHtml($postData['content']);

// Check if post was found
if (!$postData) {
    // Post not found, redirect to 404 or show error
    header('Location: index?error=post_not_found');
    exit;
}

// Record view for this post
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

// Get client IP address (handles proxy scenarios)
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
    $ipAddress = $_SERVER['HTTP_X_REAL_IP'];
}

$view->recordView($postData['id'], $ipAddress, $userAgent);

$comments = $comment->getCommentsByPost($postData['id']);

// Get vote statistics
$voteStats = $vote->getPostVoteStats($postData['id']);
$userVote = $isLoggedIn ? $vote->getUserVote($postData['id'], $user->getCurrentUser()['id']) : null;

// Set page variables for head component
$pageTitle = htmlspecialchars($postData['title']);
$pageDescription = htmlspecialchars($postData['excerpt']);

include 'includes/head.php';
?>

<!-- Enhanced Post Content Styling -->
<style>
/* Enhanced Post Content Typography and Layout */
.post-content {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-size: 1.1rem;
    line-height: 1.8;
    color: #2d3748;
    max-width: 100%;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

/* Enhanced Headings */
.post-content h1,
.post-content h2,
.post-content h3,
.post-content h4,
.post-content h5,
.post-content h6 {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-weight: 700;
    line-height: 1.3;
    margin-top: 2.5rem;
    margin-bottom: 1.5rem;
    color: #1a202c;
    position: relative;
}

.post-content h1 {
    font-size: 2.5rem;
    border-bottom: 3px solid #4299e1;
    padding-bottom: 0.5rem;
}

.post-content h2 {
    font-size: 2rem;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.4rem;
}

.post-content h3 {
    font-size: 1.5rem;
    color: #2d3748;
}

.post-content h4 {
    font-size: 1.25rem;
    color: #4a5568;
}

.post-content h5,
.post-content h6 {
    font-size: 1.1rem;
    color: #718096;
}

/* Enhanced Paragraphs and Text */
.post-content p {
    margin-bottom: 1.5rem;
    text-align: justify;
    hyphens: auto;
}

.post-content strong {
    font-weight: 700;
    color: #1a202c;
}

.post-content em {
    font-style: italic;
    color: #4a5568;
}

/* Enhanced Lists */
.post-content ul,
.post-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.post-content li {
    margin-bottom: 0.5rem;
    line-height: 1.7;
}

.post-content ul li {
    position: relative;
}

.post-content ul li::before {
    content: '•';
    color: #4299e1;
    font-weight: bold;
    position: absolute;
    left: -1.5rem;
}

.post-content ol li {
    counter-increment: list-counter;
}

.post-content ol li::before {
    content: counter(list-counter) '.';
    color: #4299e1;
    font-weight: bold;
    position: absolute;
    left: -1.5rem;
}

/* Enhanced Blockquotes */
.post-content blockquote {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-left: 4px solid #4299e1;
    margin: 2rem 0;
    padding: 1.5rem 2rem;
    border-radius: 0 8px 8px 0;
    position: relative;
    font-style: italic;
    color: #4a5568;
}

.post-content blockquote::before {
    content: '"';
    font-size: 4rem;
    color: #4299e1;
    position: absolute;
    top: -0.5rem;
    left: 1rem;
    font-family: Georgia, serif;
    opacity: 0.3;
}

.post-content blockquote p:last-child {
    margin-bottom: 0;
}

/* Enhanced Code Blocks */
.post-content pre {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    border: 1px solid #4a5568;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem 0;
    overflow-x: auto;
    position: relative;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.post-content pre::before {
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

    .post-content pre code {
        color: #e2e8f0;
        font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.6;
        background: transparent;
        padding: 0;
        border: none;
    }
    
    /* Enhanced Code Block Classes */
    .post-content .enhanced-code {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        border: 1px solid #4a5568;
        border-radius: 8px;
        padding: 1.5rem;
        margin: 2rem 0;
        overflow-x: auto;
        position: relative;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        min-height: 120px; /* Ensure minimum height for title + content */
    }
    
    .post-content .enhanced-code::before {
        content: attr(data-title);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        background: transparent;
        color: white;
        padding: 1rem 1rem; /* Increased padding for more height */
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-align: left;
        border-radius: 8px 8px 0 0;
        border-bottom: 2px solid #4a5568;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        z-index: 1;
        line-height: 1.2; /* Ensure proper line height */
    }
    
    .post-content .enhanced-code pre {
        margin-top: 6rem; /* Increased margin to make room for title */
        padding-top: 0;
        position: relative;
        z-index: 2;
        background: transparent;
    }
    
    .post-content .enhanced-code code {
        background: transparent;
        color: inherit;
        padding: 0;
        border: none;
        font-size: inherit;
        display: block;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    /* Responsive code block titles for mobile */
    @media (max-width: 768px) {
        .post-content .enhanced-code::before {
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
        }
        
        .post-content .enhanced-code pre {
            margin-top: 4rem; /* Adjust margin for smaller screens */
        }
    }
    
    @media (max-width: 480px) {
        .post-content .enhanced-code::before {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }
        
        .post-content .enhanced-code pre {
            margin-top: 4rem; /* Adjust margin for smaller screens */
        }
    }
    
    /* Enhanced styling for admin-created code blocks */
    .post-content .code-block {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        border: 1px solid #4a5568;
        border-radius: 8px;
        position: relative;
        overflow-x: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .post-content .code-block pre {
        margin: 0;
        color: #e2e8f0;
        font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.6;
        text-shadow: none;
        position: relative;
        z-index: 2;
    }
    
    .post-content .code-block code {
        background: transparent;
        color: inherit;
        padding-top: 4rem;
        border: none;
        font-size: inherit;
    }
    
    /* Ensure copy buttons work well with code blocks */
    .post-content .code-block .copy-btn {
        top: 0.5rem !important;
        right: 0.5rem !important;
    }
    
    .post-content .code-block:hover .copy-btn {
        opacity: 1 !important;
    }
    
    /* Ensure copy buttons don't interfere with titles */
    .post-content .enhanced-code .copy-btn {
        top: 0.5rem !important;
        right: 0.5rem !important;
        z-index: 15 !important;
        background: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid #4a5568 !important;
    }
    
    .post-content .enhanced-code:hover .copy-btn {
        opacity: 1 !important;
    }
    
    /* Ensure proper spacing for code content */
    .post-content .enhanced-code pre {
        padding-top: 1.5rem; /* Increased padding for better separation */
        border-top: 1px solid rgba(255, 255, 255, 0.1); /* Subtle separator line */
        position: relative;
    }
    
    /* Add extra safety margin for larger screens */
    @media (min-width: 769px) {
        .post-content .enhanced-code pre {
            margin-top: 7rem; /* Extra margin for desktop */
        }
    }
    
    /* Prevent title overlap */
    .post-content .enhanced-code {
        padding-top: 0;
    }
    
    /* Add some breathing room after the title */
    .post-content .enhanced-code::after {
        content: '';
        display: block;
        height: 1rem; /* Increased gap after title */
        background: transparent;
    }
    
    /* Ensure minimum spacing on all devices */
    .post-content .enhanced-code {
        min-height: 140px; /* Increased minimum height for better spacing */
    }
    
    .post-content .enhanced-inline-code {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        color: #2d3748;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.9em;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    
    /* Enhanced features badge styling */
    .enhanced-features-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }
    
    .enhanced-features-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* Lesson Materials Styles */
    .lesson-materials-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        padding: 2rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .materials-header {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem !important;
    }
    
    .material-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .material-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #007bff;
    }
    
    .material-icon {
        text-align: center;
        padding: 1.5rem 1rem 1rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .material-content {
        padding: 1rem 1.25rem;
        flex-grow: 1;
    }
    
    .material-title {
        font-size: 1rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    
    .material-description {
        font-size: 0.875rem;
        color: #6c757d;
        line-height: 1.4;
        margin-bottom: 0.75rem;
    }
    
    .material-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .user-download-info {
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 6px;
        border-left: 3px solid #28a745;
    }
    
    .material-footer {
        padding: 1rem 1.25rem;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }
    
    .material-footer .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .material-footer .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .material-footer .btn-group {
        width: 100%;
    }
    
    .material-footer .btn-group .btn {
        flex: 1;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .material-footer .btn-group .btn:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    
    .material-footer .btn-group .btn:last-child {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    
    /* Responsive material cards */
    @media (max-width: 768px) {
        .lesson-materials-section {
            padding: 1.5rem;
        }
        
        .material-icon {
            padding: 1rem;
        }
        
        .material-icon i {
            font-size: 2rem !important;
        }
        
        .material-content {
            padding: 0.75rem 1rem;
        }
        
        .material-footer {
            padding: 0.75rem 1rem;
        }
    }
    
    /* Course Navigation Styles */
    .course-navigation-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        padding: 2rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .course-context-header {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .course-info h5 {
        color: #2d3748;
        font-weight: 600;
    }
    
    .lesson-progress-bar {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .progress-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6c757d;
    }
    
    .progress-percentage {
        font-size: 0.875rem;
        font-weight: 600;
        color: #28a745;
    }
    
    .lesson-navigation {
        margin-top: 1.5rem;
    }
    
    .lesson-nav-btn {
        display: block;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        height: 100%;
    }
    
    .lesson-nav-btn:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.15);
        text-decoration: none;
        color: inherit;
    }
    
    .lesson-nav-btn.disabled {
        background: #f8f9fa;
        border-color: #e9ecef;
        cursor: default;
        opacity: 0.7;
    }
    
    .lesson-nav-btn.disabled:hover {
        transform: none;
        box-shadow: none;
        border-color: #e9ecef;
    }
    
    .lesson-nav-btn .nav-direction {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6c757d;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }
    
    .lesson-nav-btn.next-lesson .nav-direction {
        justify-content: flex-end;
    }
    
    .lesson-nav-btn .nav-title {
        font-size: 1rem;
        font-weight: 600;
        color: #2d3748;
        line-height: 1.3;
    }
    
    .lesson-nav-btn.next-lesson .nav-title {
        text-align: right;
    }
    
    .lesson-nav-btn:hover .nav-title {
        color: #007bff;
    }
    
    .lesson-nav-btn.disabled .nav-title {
        color: #6c757d;
    }
    
    /* Enhanced Course Hero Section */
    .course-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .course-hero::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }
    
    .course-hero-content {
        position: relative;
        z-index: 2;
    }
    
    .course-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 1rem;
        backdrop-filter: blur(10px);
    }
    
    .course-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: white;
    }
    
    .course-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 1.5rem;
    }
    
    .lesson-number {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.875rem;
        backdrop-filter: blur(10px);
    }
    
    .course-progress-overview {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        margin-top: 1.5rem;
    }
    
    .progress-stats {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    
    .stat-item {
        text-align: center;
        flex: 1;
    }
    
    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
    }
    
    .stat-label {
        font-size: 0.875rem;
        opacity: 0.8;
    }
    
    .overall-progress-bar {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .overall-progress-bar .progress-bar {
        background: linear-gradient(90deg, #4ade80 0%, #22c55e 100%);
        height: 10px;
    }
    
    .course-hero-actions {
        position: absolute;
        top: 2rem;
        right: 2rem;
        z-index: 2;
    }
    
    .course-hero-actions .btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        backdrop-filter: blur(10px);
        margin-left: 0.5rem;
    }
    
    .course-hero-actions .btn:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        color: white;
    }
    
    /* Current Lesson Progress */
    .current-lesson-progress {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .progress-header h6 {
        color: #2d3748;
        font-weight: 600;
        margin: 0;
    }
    
    .progress-percentage {
        text-align: right;
    }
    
    .percentage-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: #28a745;
    }
    
    .percentage-label {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .progress-visual .progress {
        background: #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .progress-visual .progress-bar {
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        transition: width 0.6s ease;
    }
    
    .completion-celebration {
        text-align: center;
        padding: 1rem;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        border: 1px solid #fbbf24;
    }
    
    .celebration-content h6 {
        color: #059669;
        margin-bottom: 0.5rem;
    }
    
    /* Learning Path Navigation */
    .learning-path-navigation {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .learning-path-navigation h6 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .nav-status {
        margin-top: 0.5rem;
    }
    
    .status-completed {
        color: #28a745;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .status-available {
        color: #007bff;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    /* Enhanced Materials Section */
    .materials-summary {
        display: flex;
        align-items: center;
    }
    
    .summary-item {
        text-align: center;
        padding: 0.5rem 1rem;
        background: rgba(13, 110, 253, 0.1);
        border-radius: 8px;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }
    
    .summary-number {
        display: block;
        font-size: 1.25rem;
        font-weight: 700;
        color: #0d6efd;
    }
    
    .summary-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .material-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }
    
    .material-order-badge {
        background: #e9ecef;
        color: #6c757d;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .learning-indicators {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .indicator-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .indicator-badge.requirement {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .indicator-badge.related {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .meta-row {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    /* Course Recommendations */
    .course-recommendations-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        padding: 2rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .recommendations-header h5 {
        color: #2d3748;
        font-weight: 600;
    }
    
    .recommendation-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .recommendation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }
    
    .card-icon {
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .card-icon i {
        font-size: 2rem;
    }
    
    .card-title {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .card-text {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    
    .materials-count {
        display: inline-block;
        background: #e9ecef;
        color: #6c757d;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    /* Floating Progress Indicator */
    .floating-progress-indicator {
        position: fixed;
        top: 100px;
        right: 30px;
        z-index: 1000;
        background: transparent;
        border-radius: 50%;
        padding: 0rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        opacity: 0;
        transform: scale(0.8);
        animation: fadeInScale 0.5s ease forwards;
    }
    
    .floating-progress-indicator:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
    }
    
    @keyframes fadeInScale {
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .progress-container {
        text-align: center;
        
    }
    
    .progress-circle {
        position: relative;
        width: 60px;
        height: 60px;
        margin: 0rem;
    }
    
    .progress-ring {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }
    
    .progress-ring-bg {
        fill: none;
        stroke: #e9ecef;
        stroke-width: 3;
    }
    
    .progress-ring-fill {
        fill: none;
        stroke: #28a745;
        stroke-width: 3;
        stroke-linecap: round;
        stroke-dasharray: 100;
        stroke-dashoffset: 100;
        transition: stroke-dashoffset 0.6s ease;
    }
    
    .progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.75rem;
        font-weight: 600;
        color: #28a745;
    }
    
    .progress-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
        text-align: center;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .course-navigation-section {
            padding: 1.5rem;
        }
        
        .course-hero {
            padding: 1.5rem;
        }
        
        .course-hero-actions {
            position: static;
            margin-top: 1rem;
            text-align: center;
        }
        
        .course-hero-actions .btn {
            margin: 0.25rem;
        }
        
        .progress-stats {
            flex-direction: column;
            gap: 1rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .course-context-header .d-flex {
            flex-direction: column;
            gap: 1rem;
        }
        
        .lesson-nav-btn {
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .lesson-nav-btn .nav-title,
        .lesson-nav-btn.next-lesson .nav-title {
            text-align: left;
        }
        
        .lesson-nav-btn.next-lesson .nav-direction {
            justify-content: flex-start;
        }
        
        .materials-summary {
            margin-top: 1rem;
            justify-content: center;
        }
        
        .recommendation-card {
            margin-bottom: 1rem;
        }
        
        /* Floating progress indicator responsive */
        .floating-progress-indicator {
            top: 80px;
            right: 20px;
            padding: 0rem;
        }
        
        .progress-circle {
            width: 50px;
            height: 50px;
        }
        
        .progress-label {
            font-size: 0.7rem;
        }
    }
    
    .post-content .enhanced-table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .post-content .enhanced-blockquote {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        border-left: 4px solid #4299e1;
        margin: 2rem 0;
        padding: 1.5rem 2rem;
        border-radius: 0 8px 8px 0;
        position: relative;
        font-style: italic;
        color: #4a5568;
    }

/* Enhanced Inline Code */
.post-content code {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    color: #2d3748;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.9em;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Enhanced Tables */
.post-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.post-content th {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    border: none;
}

.post-content td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
}

.post-content tr:nth-child(even) {
    background-color: #f7fafc;
}

.post-content tr:hover {
    background-color: #edf2f7;
}

    /* Enhanced Images */
    .post-content img {
        width: 1400px;
        height: 420px;
        max-width: 100%;
        max-height: 420px;
        margin: 2rem auto;
        display: block;
        box-shadow: none !important;
        filter: none !important;
        -webkit-filter: none !important;
        object-fit: cover;
        object-position: center;
    }
    
    /* Image Container for better control */
    .post-content .image-container {
        width: 100%;
        max-width: 1400px;
        height: 420px;
        margin: 2rem auto;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
    }
    
    .post-content .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        margin: 0;
    }

/* Enhanced Links */
.post-content a {
    color: #4299e1;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
    font-weight: 500;
}

.post-content a:hover {
    color: #3182ce;
    border-bottom-color: #3182ce;
    text-decoration: none;
}

/* Enhanced Horizontal Rules */
.post-content hr {
    border: none;
    height: 2px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
    margin: 3rem 0;
}

/* Enhanced Definition Lists */
.post-content dl {
    margin: 2rem 0;
}

.post-content dt {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.5rem;
}

.post-content dd {
    margin-left: 1rem;
    margin-bottom: 1rem;
    color: #4a5568;
    padding-left: 1rem;
    border-left: 2px solid #e2e8f0;
}

/* Enhanced Keyboard Elements */
.post-content kbd {
    background: #2d3748;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.9em;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Enhanced Mark Elements */
.post-content mark {
    background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%);
    color: #c05621;
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
}

/* Enhanced Small Text */
.post-content small {
    font-size: 0.875em;
    color: #718096;
    font-weight: 500;
}

/* Enhanced Subscript and Superscript */
.post-content sub,
.post-content sup {
    font-size: 0.75em;
    color: #4a5568;
}

/* Enhanced Abbreviations */
.post-content abbr {
    border-bottom: 1px dotted #718096;
    cursor: help;
}

/* Enhanced Citations */
.post-content cite {
    font-style: italic;
    color: #718096;
    font-size: 0.9em;
}

/* Enhanced Time Elements */
.post-content time {
    color: #718096;
    font-size: 0.9em;
}

/* Enhanced Figure and Figcaption */
.post-content figure {
    margin: 2rem 0;
    text-align: center;
}

.post-content figcaption {
    margin-top: 0.5rem;
    font-size: 0.9em;
    color: #718096;
    font-style: italic;
}

/* Enhanced Video and Audio */
.post-content video,
.post-content audio {
    max-width: 100%;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Enhanced Math Elements */
.post-content .math {
    font-family: 'Times New Roman', serif;
    font-style: italic;
}

/* Enhanced Callout Boxes */
.post-content .callout {
    background: linear-gradient(135deg, #f0fff4 0%, #dcfce7 100%);
    border: 1px solid #68d391;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem 0;
    position: relative;
}

.post-content .callout::before {
    content: '💡';
    position: absolute;
    top: 1rem;
    left: 1rem;
    font-size: 1.5rem;
}

.post-content .callout p {
    margin-left: 2.5rem;
    margin-bottom: 0;
}

/* Enhanced Warning Boxes */
.post-content .warning {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border: 1px solid #fc8181;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem 0;
    position: relative;
}

.post-content .warning::before {
    content: '⚠️';
    position: absolute;
    top: 1rem;
    left: 1rem;
    font-size: 1.5rem;
}

.post-content .warning p {
    margin-left: 2.5rem;
    margin-bottom: 0;
}

/* Enhanced Info Boxes */
.post-content .info {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    border: 1px solid #63b3ed;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem 0;
    position: relative;
}

.post-content .info::before {
    content: 'ℹ️';
    position: absolute;
    top: 1rem;
    left: 1rem;
    font-size: 1.5rem;
}

    .post-content .info p {
        margin-left: 2.5rem;
        margin-bottom: 0;
    }
    
    /* Table of Contents Styling */
    .table-of-contents {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .table-of-contents h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #4299e1;
    }
    
    .table-of-contents ul {
        margin: 0;
        padding: 0;
    }
    
    .table-of-contents li {
        margin-bottom: 0.5rem;
    }
    
    .table-of-contents a {
        color: #495057;
        text-decoration: none;
        padding: 0.5rem;
        border-radius: 4px;
        transition: all 0.2s ease;
        display: block;
    }
    
    .table-of-contents a:hover {
        background-color: #4299e1;
        color: white;
        transform: translateX(5px);
    }
    
    .table-of-contents a.active {
        background-color: #4299e1;
        color: white;
        font-weight: 600;
    }
    
    /* Enhanced Reading Progress Bar */
    .reading-progress-bar {
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #4299e1, #3182ce);
        z-index: 9999;
        transition: width 0.3s ease;
        box-shadow: 0 2px 4px rgba(66, 153, 225, 0.3);
    }
    
    /* Enhanced Copy Button Styling */
    .post-content .copy-btn {
        background: rgba(66, 153, 225, 0.9);
        border: none;
        color: white;
        padding: 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }
    
    .post-content .copy-btn:hover {
        background: rgba(66, 153, 225, 1);
        transform: scale(1.1);
    }
    
    .post-content .copy-btn.btn-success {
        background: rgba(56, 178, 172, 0.9);
    }
    
    /* Ensure no shadows on images from external CSS */
    .post-content img,
    .post-content img:hover,
    .post-content img:focus,
    .post-content img:active {
        box-shadow: none !important;
        filter: none !important;
        -webkit-filter: none !important;
        -moz-box-shadow: none !important;
        -webkit-box-shadow: none !important;
        text-shadow: none !important;
        transform: none !important;
        transition: none !important;
    }
    
    /* Enhanced Heading Interactions */
    .post-content h1:hover,
    .post-content h2:hover,
    .post-content h3:hover,
    .post-content h4:hover,
    .post-content h5:hover,
    .post-content h6:hover {
        cursor: pointer;
        transition: color 0.2s ease;
    }
    
    /* Enhanced Code Block Interactions */
    .post-content pre:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    
    /* Mobile Touch Enhancements */
    @media (max-width: 768px) {
        .post-content h1,
        .post-content h2,
        .post-content h3,
        .post-content h4,
        .post-content h5,
        .post-content h6 {
            cursor: default;
            -webkit-tap-highlight-color: transparent;
        }
        
        .post-content pre {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .post-content .copy-btn {
            min-height: 44px;
            min-width: 44px;
            touch-action: manipulation;
        }
        
        .post-content .table-of-contents a {
            min-height: 44px;
            display: flex;
            align-items: center;
            touch-action: manipulation;
        }
        
        .post-content img {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
    }

/* Responsive Design */
@media (max-width: 768px) {
    .post-content {
        font-size: 1rem;
        line-height: 1.7;
    }
    
    .post-content img {
        width: 100%;
        height: 250px;
        max-width: 100%;
        max-height: 250px;
        object-fit: cover;
        object-position: center;
    }
    
    .post-content .image-container {
        height: 250px;
        margin: 1.5rem auto;
    }
    
    .post-content h1 {
        font-size: 2rem;
    }
    
    .post-content h2 {
        font-size: 1.75rem;
    }
    
    .post-content h3 {
        font-size: 1.4rem;
    }
    
    .post-content pre {
        padding: 1rem;
        font-size: 0.8rem;
    }
    
    .post-content blockquote {
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
    }
    
    .post-content table {
        font-size: 0.9rem;
    }
    
    .post-content th,
    .post-content td {
        padding: 0.75rem;
    }
}

/* Small Mobile Devices */
@media (max-width: 576px) {
    .post-content {
        font-size: 0.95rem;
        line-height: 1.6;
        padding: 0 0.5rem;
    }
    
    .post-content img {
        height: 200px;
        max-height: 200px;
        margin: 1rem auto;
    }
    
    .post-content .image-container {
        height: 200px;
        margin: 1rem auto;
    }
    
    .post-content h1 {
        font-size: 1.75rem;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    
    .post-content h2 {
        font-size: 1.5rem;
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
    }
    
    .post-content h3 {
        font-size: 1.25rem;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .post-content p {
        margin-bottom: 1.25rem;
    }
    
    .post-content pre {
        padding: 0.75rem;
        font-size: 0.75rem;
        margin: 1.5rem 0;
    }
    
    .post-content .enhanced-code::before {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
    }
    
    .post-content blockquote {
        padding: 1rem;
        margin: 1.25rem 0;
        font-size: 0.95rem;
    }
    
    .post-content table {
        font-size: 0.85rem;
        margin: 1.5rem 0;
    }
    
    .post-content th,
    .post-content td {
        padding: 0.5rem;
    }
    
    .post-content ul,
    .post-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1.25rem;
    }
    
    .post-content li {
        margin-bottom: 0.4rem;
    }
}

/* Extra Small Mobile Devices */
@media (max-width: 480px) {
    .post-content {
        font-size: 0.9rem;
        line-height: 1.5;
        padding: 0 0.25rem;
    }
    
    .post-content img {
        height: 180px;
        max-height: 180px;
        margin: 0.75rem auto;
    }
    
    .post-content .image-container {
        height: 180px;
        margin: 0.75rem auto;
    }
    
    .post-content h1 {
        font-size: 1.6rem;
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
    }
    
    .post-content h2 {
        font-size: 1.4rem;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .post-content h3 {
        font-size: 1.2rem;
        margin-top: 1.25rem;
        margin-bottom: 0.75rem;
    }
    
    .post-content pre {
        padding: 0.5rem;
        font-size: 0.7rem;
        margin: 1.25rem 0;
    }
    
    .post-content .enhanced-code::before {
        font-size: 0.7rem;
        padding: 0.4rem 0.75rem;
    }
    
    .post-content blockquote {
        padding: 0.75rem;
        margin: 1rem 0;
        font-size: 0.9rem;
    }
    
    .post-content table {
        font-size: 0.8rem;
        margin: 1.25rem 0;
    }
    
    .post-content th,
    .post-content td {
        padding: 0.4rem;
    }
    
    .post-content ul,
    .post-content ol {
        padding-left: 1.25rem;
        margin-bottom: 1rem;
    }
    
    .post-content li {
        margin-bottom: 0.3rem;
    }
    
    .post-content .table-of-contents {
        margin: 1rem 0;
        padding: 1rem;
    }
    
    .post-content .table-of-contents h5 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .post-content .table-of-contents a {
        padding: 0.4rem;
        font-size: 0.9rem;
    }
}

/* Print Styles */
@media print {
    .post-content {
        font-size: 12pt;
        line-height: 1.6;
        color: black;
    }
    
    .post-content h1,
    .post-content h2,
    .post-content h3,
    .post-content h4,
    .post-content h5,
    .post-content h6 {
        color: black;
        page-break-after: avoid;
    }
    
    .post-content img {
        max-width: 100%;
        max-height: none;
        page-break-inside: avoid;
        object-fit: contain;
    }
    
    .post-content pre {
        background: #f5f5f5;
        border: 1px solid #ddd;
        color: black;
    }
}
</style>

<?php include 'includes/header.php'; ?>

<!-- Reading Progress Bar -->
<div class="reading-progress-bar" id="readingProgressBar"></div>

<div class="container-fluid mt-5 pt-5">
    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-8 col-xl-9">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="breadcrumb-nav mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index"><i class="fas fa-home"></i> Home</a></li>
                    <?php if ($courseContext): ?>
                        <li class="breadcrumb-item"><a href="courses"><i class="fas fa-graduation-cap"></i> Courses</a></li>
                        <li class="breadcrumb-item"><a href="course-view?course=<?php echo $courseContext['slug']; ?>"><?php echo htmlspecialchars($courseContext['title']); ?></a></li>
                        <li class="breadcrumb-item text-muted"><?php echo htmlspecialchars($courseModuleInfo['title']); ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item"><a href="categories?category=<?php echo $postData['category_slug']; ?>"><?php echo htmlspecialchars($postData['category_name']); ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($postData['title']); ?></li>
                </ol>
            </nav>

            <!-- Post Header -->
            <header class="post-header mb-5">
                <div class="post-meta mb-3">
                    <span class="category-badge"><?php echo htmlspecialchars($postData['category_name']); ?></span>
                    <?php if ($postData['is_premium']): ?>
                    <span class="premium-badge">
                        <i class="fas fa-crown"></i>Premium
                    </span>
                    <?php endif; ?>
                    <span class="reading-time">
                        <i class="fas fa-clock"></i>
                        <?php 
                        $wordCount = str_word_count(strip_tags($postData['content']));
                        $readingTime = ceil($wordCount / 200); // Average reading speed: 200 words per minute
                        echo $readingTime . ' min read';
                        ?>
                    </span>
                    <?php if (strpos($postData['content'], 'data-title') !== false): ?>
                    <span class="enhanced-features-badge" title="This post contains enhanced code examples with custom titles">
                        <i class="fas fa-code"></i>Enhanced Code
                    </span>
                    <?php endif; ?>
                </div>
                
                <h1 class="post-title"><?php echo htmlspecialchars($postData['title']); ?></h1>
                <p class="post-excerpt"><?php echo htmlspecialchars($postData['excerpt']); ?></p>
                
                <div class="post-info">
                    <div class="post-author">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($postData['author_name']); ?></span>
                    </div>
                    <div class="post-date">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('M j, Y', strtotime($postData['published_at'])); ?></span>
                    </div>
                    <div class="post-views">
                        <i class="fas fa-eye"></i>
                        <span><?php echo number_format($postData['view_count'] ?? 0); ?> views</span>
                    </div>
                </div>
                
                <?php if ($postData['tags']): ?>
                <div class="post-tags">
                    <i class="fas fa-tags"></i>
                    <?php 
                    $tags = explode(',', $postData['tags']);
                    foreach ($tags as $tagName):
                        $tagName = trim($tagName);
                        if (!empty($tagName)):
                            // Get tag properties from database
                            $tagData = $tag->getTagByName($tagName);
                            $tagColor = $tagData ? $tagData['color'] : '#6c757d';
                            $tagSlug = $tagData ? $tagData['slug'] : strtolower(str_replace(' ', '-', $tagName));
                    ?>
                    <a href="tags?tag=<?php echo urlencode($tagSlug); ?>" class="badge text-decoration-none me-1" style="background-color: <?php echo htmlspecialchars($tagColor); ?>; color: white;">
                        <?php echo htmlspecialchars($tagName); ?>
                    </a>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                    
                    <!-- Bookmark Button (Icon Only) -->
                    <?php if ($isLoggedIn): ?>
                    <button class="btn btn-sm bookmark-btn-icon" 
                            data-post-id="<?php echo $postData['id']; ?>"
                            title="Bookmark this tutorial">
                        <i class="far fa-bookmark"></i>
                    </button>
                    <?php else: ?>
                    <button class="btn btn-sm bookmark-btn-icon" 
                            data-bs-toggle="modal" 
                            data-bs-target="#loginModal"
                            title="Login to bookmark">
                        <i class="far fa-bookmark"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <!-- Bookmark Button (Icon Only) - when no tags -->
                <div class="post-tags">
                    <?php if ($isLoggedIn): ?>
                    <button class="btn btn-sm bookmark-btn-icon" 
                            data-post-id="<?php echo $postData['id']; ?>"
                            title="Bookmark this tutorial">
                        <i class="far fa-bookmark"></i>
                    </button>
                    <?php else: ?>
                    <button class="btn btn-sm bookmark-btn-icon" 
                            data-bs-toggle="modal" 
                            data-bs-target="#loginModal"
                            title="Login to bookmark">
                        <i class="far fa-bookmark"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </header>
            
            <!-- Floating Reading Progress Indicator -->
            <?php if ($courseContext && $isLoggedIn): ?>
            <div class="floating-progress-indicator" id="floatingProgress">
                <div class="progress-container">
                    <div class="progress-circle">
                        <svg viewBox="0 0 36 36" class="progress-ring">
                            <path class="progress-ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="progress-ring-fill" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <div class="progress-text">
                            <span class="progress-number">0%</span>
                        </div>
                    </div>
                    <!-- <div class="progress-label">Reading Progress</div> -->
                </div>
            </div>
            <?php endif; ?>

            <!-- Post Content -->
            <article class="post-content mb-5">
                <?php echo $renderedContent; ?>
            </article>
            
            <!-- Enhanced Course Navigation (if part of a course) -->
            <?php if ($courseContext): ?>
            <div class="course-navigation-section mb-5">
                <!-- Course Hero Section -->
                <div class="course-hero mb-4">
                    <div class="course-hero-content">
                        <div class="course-badge">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Course Lesson</span>
                        </div>
                        <h4 class="course-title mb-2">
                            <?php echo htmlspecialchars($courseContext['title']); ?>
                        </h4>
                        <p class="course-subtitle mb-3">
                            <i class="fas fa-layer-group me-2"></i>
                            Module: <?php echo htmlspecialchars($courseModuleInfo['title']); ?>
                            <?php if ($userProgress): ?>
                                <span class="lesson-number ms-2">
                                    <i class="fas fa-play-circle me-1"></i>
                                    Lesson <?php echo $userProgress['lesson_order'] ?? 'N/A'; ?>
                                </span>
                            <?php endif; ?>
                        </p>
                        
                        <!-- Course Progress Overview -->
                        <?php if ($isLoggedIn): ?>
                            <?php
                            // Get overall course progress
                            $overallProgress = $course->getUserCourseProgress($user->getCurrentUser()['id'], $courseContext['id']);
                            $totalLessons = count($overallProgress);
                            $completedLessons = count(array_filter($overallProgress, function($p) { return $p['progress_percentage'] == 100; }));
                            $overallPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                            ?>
                            <div class="course-progress-overview">
                                <div class="progress-stats">
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $completedLessons; ?></span>
                                        <span class="stat-label">Completed</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $totalLessons; ?></span>
                                        <span class="stat-label">Total Lessons</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $overallPercentage; ?>%</span>
                                        <span class="stat-label">Course Progress</span>
                                    </div>
                                </div>
                                <div class="overall-progress-bar">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-primary" style="width: <?php echo $overallPercentage; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="course-hero-actions">
                        <a href="course-view?course=<?php echo $courseContext['slug']; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list me-1"></i>View Full Course
                        </a>
                        <?php if ($isLoggedIn): ?>
                            <a href="profile" class="btn btn-outline-secondary">
                                <i class="fas fa-user-graduate me-1"></i>My Progress
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Current Lesson Progress -->
                <?php if ($userProgress): ?>
                <div class="current-lesson-progress mb-4">
                    <div class="progress-header">
                        <h6 class="mb-2">
                            <i class="fas fa-chart-line me-2"></i>
                            Current Lesson Progress
                        </h6>
                        <div class="progress-percentage">
                            <span class="percentage-number"><?php echo $userProgress['progress_percentage']; ?>%</span>
                            <span class="percentage-label">Complete</span>
                        </div>
                    </div>
                    
                    <div class="progress-visual">
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar bg-success" 
                                 style="width: <?php echo $userProgress['progress_percentage']; ?>%"
                                 data-progress="<?php echo $userProgress['progress_percentage']; ?>">
                            </div>
                        </div>
                        
                        <?php if ($userProgress['completed_at']): ?>
                            <div class="completion-celebration mt-3">
                                <div class="celebration-content">
                                    <i class="fas fa-trophy text-warning fa-2x mb-2"></i>
                                    <h6 class="text-success mb-1">Lesson Completed!</h6>
                                    <p class="text-muted mb-0">
                                        Completed on <?php echo date('M j, Y', strtotime($userProgress['completed_at'])); ?>
                                        <?php if ($userProgress['time_spent_minutes']): ?>
                                            <br><small>Time spent: <?php echo $userProgress['time_spent_minutes']; ?> minutes</small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Learning Path Navigation -->
                <div class="learning-path-navigation">
                    <h6 class="mb-3">
                        <i class="fas fa-route me-2"></i>
                        Learning Path
                    </h6>
                    
                    <div class="lesson-navigation">
                        <div class="row">
                            <div class="col-md-6">
                                <?php if ($prevLesson): ?>
                                    <a href="post?slug=<?php echo $prevLesson['slug']; ?>" class="lesson-nav-btn prev-lesson">
                                        <div class="nav-direction">
                                            <i class="fas fa-chevron-left me-2"></i>Previous Lesson
                                        </div>
                                        <div class="nav-title"><?php echo htmlspecialchars($prevLesson['title']); ?></div>
                                        <div class="nav-status">
                                            <?php
                                            $prevProgress = isset($overallProgress[$prevLesson['id']]) ? $overallProgress[$prevLesson['id']] : null;
                                            if ($prevProgress && $prevProgress['progress_percentage'] == 100):
                                            ?>
                                                <span class="status-completed">
                                                    <i class="fas fa-check-circle me-1"></i>Completed
                                                </span>
                                            <?php else: ?>
                                                <span class="status-available">
                                                    <i class="fas fa-play-circle me-1"></i>Available
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <?php if ($nextLesson): ?>
                                    <a href="post?slug=<?php echo $nextLesson['slug']; ?>" class="lesson-nav-btn next-lesson">
                                        <div class="nav-direction">
                                            Next Lesson<i class="fas fa-chevron-right ms-2"></i>
                                        </div>
                                        <div class="nav-title"><?php echo htmlspecialchars($nextLesson['title']); ?></div>
                                        <div class="nav-status">
                                            <?php
                                            $nextProgress = isset($overallProgress[$nextLesson['id']]) ? $overallProgress[$nextLesson['id']] : null;
                                            if ($nextProgress && $nextProgress['progress_percentage'] == 100):
                                            ?>
                                                <span class="status-completed">
                                                    <i class="fas fa-check-circle me-1"></i>Completed
                                                </span>
                                            <?php else: ?>
                                                <span class="status-available">
                                                    <i class="fas fa-play-circle me-1"></i>Available
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <div class="lesson-nav-btn next-lesson disabled">
                                        <div class="nav-direction">
                                            <i class="fas fa-trophy me-2"></i>Module Complete!
                                        </div>
                                        <div class="nav-title">Congratulations! You've finished this module.</div>
                                        <div class="nav-status">
                                            <span class="status-completed">
                                                <i class="fas fa-star me-1"></i>All lessons completed
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Lesson Materials Section -->
            <?php if (!empty($lessonMaterials)): ?>
            <div class="lesson-materials-section mb-5">
                <div class="materials-header mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">
                                <i class="fas fa-file-download me-2"></i>
                                Lesson Materials
                                <span class="badge bg-primary ms-2"><?php echo count($lessonMaterials); ?></span>
                            </h5>
                            <p class="text-muted mb-0">Download additional resources to enhance your learning experience</p>
                        </div>
                        <div class="materials-summary">
                            <div class="summary-item">
                                <i class="fas fa-download text-primary"></i>
                                <span class="summary-number"><?php echo array_sum(array_column($lessonMaterials, 'download_count')); ?></span>
                                <span class="summary-label">Total Downloads</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <?php foreach ($lessonMaterials as $material): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="material-card h-100">
                                <div class="material-icon">
                                    <?php
                                    $iconClass = 'fa-file';
                                    switch (strtolower($material['file_type'])) {
                                        case 'pdf':
                                            $iconClass = 'fa-file-pdf text-danger';
                                            break;
                                        case 'ppt':
                                        case 'pptx':
                                            $iconClass = 'fa-file-powerpoint text-warning';
                                            break;
                                        case 'doc':
                                        case 'docx':
                                            $iconClass = 'fa-file-word text-primary';
                                            break;
                                        case 'xls':
                                        case 'xlsx':
                                            $iconClass = 'fa-file-excel text-success';
                                            break;
                                        default:
                                            $iconClass = 'fa-file-alt text-secondary';
                                    }
                                    ?>
                                    <i class="fas <?php echo $iconClass; ?> fa-3x"></i>
                                </div>
                                
                                <div class="material-content">
                                    <div class="material-header">
                                        <h6 class="material-title"><?php echo htmlspecialchars($material['title']); ?></h6>
                                        <?php if ($material['order_index'] > 0): ?>
                                            <span class="material-order-badge">
                                                <i class="fas fa-sort-numeric-up me-1"></i>
                                                #<?php echo $material['order_index']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="material-description"><?php echo htmlspecialchars($material['description']); ?></p>
                                    
                                    <!-- Progressive Learning Indicators -->
                                    <?php if ($material['required_lesson_id'] || $material['related_lesson_id']): ?>
                                        <div class="learning-indicators mt-2">
                                            <?php if ($material['required_lesson_id']): ?>
                                                <span class="indicator-badge requirement">
                                                    <i class="fas fa-lock me-1"></i>
                                                    Requires Lesson Completion
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($material['related_lesson_id']): ?>
                                                <span class="indicator-badge related">
                                                    <i class="fas fa-link me-1"></i>
                                                    Related to Lesson
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="material-meta">
                                        <div class="meta-row">
                                            <small class="text-muted">
                                                <i class="fas fa-download me-1"></i>
                                                <?php echo number_format($material['download_count']); ?> downloads
                                            </small>
                                            <small class="text-muted ms-2">
                                                <i class="fas fa-eye me-1"></i>
                                                <?php echo number_format($material['preview_count'] ?? 0); ?> previews
                                            </small>
                                            <small class="text-muted ms-3">
                                                <i class="fas fa-hdd me-1"></i>
                                                <?php echo number_format($material['file_size'] / 1024, 1); ?> KB
                                            </small>
                                        </div>
                                        
                                        <?php if ($material['is_active'] == 0): ?>
                                            <div class="material-status mt-2">
                                                <span class="status-badge inactive">
                                                    <i class="fas fa-eye-slash me-1"></i>Inactive
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($isLoggedIn && isset($material['user_download_count']) && $material['user_download_count'] > 0): ?>
                                        <div class="user-download-info mt-2">
                                            <small class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Downloaded <?php echo $material['user_download_count']; ?> time<?php echo $material['user_download_count'] > 1 ? 's' : ''; ?>
                                            </small>
                                            <?php if (isset($material['last_downloaded'])): ?>
                                                <br><small class="text-muted">
                                                    Last: <?php echo date('M j, Y', strtotime($material['last_downloaded'])); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="material-footer">
                                    <?php if ($isLoggedIn): ?>
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                                                                         <a href="preview_material?id=<?php echo $material['id']; ?>" 
                                                class="btn btn-outline-success btn-sm"
                                                title="Preview Material"
                                                target="_blank">
                                                <i class="fas fa-eye me-1"></i>Preview
                                            </a>
                                            <a href="download_material?id=<?php echo $material['id']; ?>" 
                                               class="btn btn-primary btn-sm"
                                               title="Download Material">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm w-100" 
                                                onclick="showLoginModal()">
                                            <i class="fas fa-lock me-1"></i>Login to Download
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Course Recommendations & Next Steps -->
            <?php if ($courseContext && $isLoggedIn): ?>
            <div class="course-recommendations-section mb-5">
                <div class="recommendations-header mb-4">
                    <h5 class="mb-2">
                        <i class="fas fa-lightbulb me-2"></i>
                        Recommended Next Steps
                    </h5>
                    <p class="text-muted mb-0">Based on your learning progress, here are some suggestions</p>
                </div>
                
                <div class="recommendations-content">
                    <div class="row">
                        <!-- Next Lesson Recommendation -->
                        <?php if ($nextLesson): ?>
                        <div class="col-md-6 mb-3">
                            <div class="recommendation-card next-lesson-card">
                                <div class="card-icon">
                                    <i class="fas fa-arrow-right text-primary"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Continue Learning</h6>
                                    <p class="card-text">Move to the next lesson to keep your momentum going</p>
                                    <a href="post?slug=<?php echo $nextLesson['slug']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-play me-1"></i>Start Next Lesson
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Course Completion -->
                        <?php if (!$nextLesson && $overallPercentage >= 80): ?>
                        <div class="col-md-6 mb-3">
                            <div class="recommendation-card completion-card">
                                <div class="card-icon">
                                    <i class="fas fa-trophy text-warning"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Course Completion</h6>
                                    <p class="card-text">You're almost done! Complete the remaining lessons</p>
                                    <a href="course-view?course=<?php echo $courseContext['slug']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-flag-checkered me-1"></i>View Course
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Related Materials -->
                        <?php if (!empty($lessonMaterials)): ?>
                        <div class="col-md-6 mb-3">
                            <div class="recommendation-card materials-card">
                                <div class="card-icon">
                                    <i class="fas fa-file-alt text-success"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Download Materials</h6>
                                    <p class="card-text">Get additional resources to reinforce your learning</p>
                                    <span class="materials-count">
                                        <i class="fas fa-download me-1"></i>
                                        <?php echo count($lessonMaterials); ?> materials available
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Profile Progress -->
                        <div class="col-md-6 mb-3">
                            <div class="recommendation-card profile-card">
                                <div class="card-icon">
                                    <i class="fas fa-chart-line text-info"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Track Progress</h6>
                                    <p class="card-text">Monitor your learning journey and achievements</p>
                                    <a href="profile" class="btn btn-info btn-sm">
                                        <i class="fas fa-user-graduate me-1"></i>View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Post Actions -->
            <div class="post-actions mb-5">
                <div class="action-buttons">
                    <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="sharePost()">
                        <i class="fas fa-share"></i> Share
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="copyPostLink()">
                        <i class="fas fa-link"></i> Copy Link
                    </button>
                </div>
            </div>

            <!-- Voting Section -->
            <div class="voting-section mb-5">
                <div class="voting-container">
                    <div class="voting-content">
                        <div class="voting-info">
                            <h6>Was this tutorial helpful?</h6>
                            <p>Help others discover great content by voting</p>
                        </div>
                        <div class="voting-actions">
                            <?php if ($isLoggedIn): ?>
                            <?php 
                            $userVoteType = $userVote ? $userVote['vote_type'] : '';
                            ?>
                            <div class="vote-buttons">
                                <button class="btn <?php echo $userVoteType === 'upvote' ? 'btn-success' : 'btn-outline-success'; ?> vote-btn" 
                                        data-post-id="<?php echo $postData['id']; ?>" 
                                        data-vote-type="upvote" 
                                        data-current-vote="<?php echo $userVoteType; ?>"
                                        title="This tutorial was helpful">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="vote-count"><?php echo $voteStats['upvotes'] ?? 0; ?></span>
                                </button>
                                <button class="btn <?php echo $userVoteType === 'downvote' ? 'btn-danger' : 'btn-outline-danger'; ?> vote-btn" 
                                        data-post-id="<?php echo $postData['id']; ?>" 
                                        data-vote-type="downvote" 
                                        data-current-vote="<?php echo $userVoteType; ?>"
                                        title="This tutorial needs improvement">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span class="vote-count"><?php echo $voteStats['downvotes'] ?? 0; ?></span>
                                </button>
                            </div>
                            <div class="vote-score">
                                <span class="score-badge">
                                    <i class="fas fa-chart-line"></i>
                                    Score: <?php echo $voteStats['vote_score'] ?? 0; ?>
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="vote-stats">
                                <span class="stat-item">
                                    <i class="fas fa-thumbs-up text-success"></i>
                                    <?php echo $voteStats['upvotes'] ?? 0; ?>
                                </span>
                                <span class="stat-item">
                                    <i class="fas fa-thumbs-down text-danger"></i>
                                    <?php echo $voteStats['downvotes'] ?? 0; ?>
                                </span>
                            </div>
                            <div class="vote-score">
                                <span class="score-badge">
                                    <i class="fas fa-chart-line"></i>
                                    Score: <?php echo $voteStats['vote_score'] ?? 0; ?>
                                </span>
                            </div>
                            <div class="login-prompt">
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <i class="fas fa-sign-in-alt"></i>Login to Vote
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <section class="comments-section">
                <div class="comments-header">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">
                            <i class="fas fa-comments text-primary"></i>
                            Discussion
                    </h3>
                        <div class="comments-count">
                            <span class="badge bg-primary fs-6"><?php echo count($comments); ?> comments</span>
                        </div>
                    </div>
                    <p class="text-muted mb-0">Join the conversation and share your thoughts</p>
                </div>
                
                <!-- Comment Form -->
                <div class="comment-form-container mb-4">
                    <div class="comment-form-header">
                        <h6 class="mb-3">
                            <i class="fas fa-edit text-primary"></i>
                            Leave a Comment
                        </h6>
                    </div>
                    <form id="commentForm">
                        <input type="hidden" id="postId" value="<?php echo $postData['id']; ?>">
                        <div class="mb-3">
                            <textarea class="form-control comment-textarea" id="commentContent" rows="4" 
                                      placeholder="Share your thoughts, ask questions, or provide feedback..." required></textarea>
                            <div class="char-counter mt-2">
                                <small class="text-muted">
                                    <span id="charCount">0</span>/1000 characters
                                </small>
                            </div>
                        </div>
                        <div id="commentMessage" class="alert" style="display: none;"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="comment-form-info">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Your comment will be visible to everyone
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary comment-submit-btn">
                                <i class="fas fa-paper-plane"></i> Post Comment
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Comments List -->
                <div id="commentsList">
                    <?php if (empty($comments)): ?>
                    <div class="no-comments">
                        <div class="no-comments-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h5>No comments yet</h5>
                        <p>Be the first to share your thoughts and start the conversation!</p>
                        <button class="btn btn-outline-primary" onclick="document.getElementById('commentContent').focus()">
                            <i class="fas fa-plus"></i> Start Discussion
                        </button>
                    </div>
                    <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" data-comment-id="<?php echo $comment['id']; ?>">
                        <div class="comment-header">
                            <div class="comment-author-info">
                                <div class="comment-author-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="comment-author-details">
                                    <strong class="comment-author-name"><?php echo htmlspecialchars($comment['username'] ?: $comment['guest_name']); ?></strong>
                                    <span class="comment-date">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="comment-actions">
                                <button class="btn btn-sm btn-outline-primary comment-action-btn reply-btn" 
                                        data-comment-id="<?php echo $comment['id']; ?>">
                                    <i class="fas fa-reply"></i> Reply
                                </button>
                            </div>
                        </div>
                        
                        <div class="comment-content">
                            <?php echo htmlspecialchars($comment['content']); ?>
                        </div>
                        
                        <!-- Reply Form (Hidden by default) -->
                        <div class="reply-form-container" id="replyForm_<?php echo $comment['id']; ?>" style="display: none;">
                            <form class="reply-form" data-comment-id="<?php echo $comment['id']; ?>">
                                <div class="mb-3">
                                    <textarea class="form-control reply-textarea" rows="3" 
                                              placeholder="Write your reply..." required></textarea>
                                    <div class="char-counter mt-2">
                                        <small class="text-muted">
                                            <span class="reply-char-count">0</span>/1000 characters
                                        </small>
                                    </div>
                                </div>
                                <div class="reply-form-actions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary cancel-reply-btn" 
                                            data-comment-id="<?php echo $comment['id']; ?>">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-paper-plane"></i> Post Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Replies Section -->
                        <div class="replies-container" id="replies_<?php echo $comment['id']; ?>">
                            <?php 
                            $replies = $reply->getRepliesByComment($comment['id']);
                            if (!empty($replies)): 
                            ?>
                                <div class="replies-list">
                                    <?php foreach ($replies as $reply_item): ?>
                                    <div class="reply-item">
                                        <div class="reply-header">
                                            <div class="reply-author-info">
                                                <div class="reply-author-avatar">
                                                    <i class="fas fa-user-circle"></i>
                                                </div>
                                                <div class="reply-author-details">
                                                    <strong class="reply-author-name">
                                                        <?php echo htmlspecialchars($reply_item['username'] ?: $reply_item['guest_name']); ?>
                                                    </strong>
                                                    <span class="reply-date">
                                                        <i class="fas fa-clock"></i>
                                                        <?php echo date('M j, Y g:i A', strtotime($reply_item['created_at'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="reply-content">
                                            <?php echo htmlspecialchars($reply_item['content']); ?>
                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Show Replies Button -->
                        <?php if ($comment['reply_count'] > 0): ?>
                        <div class="show-replies-container">
                            <button class="btn btn-sm btn-link text-primary show-replies-btn" 
                                    data-comment-id="<?php echo $comment['id']; ?>">
                                <i class="fas fa-chevron-down"></i>
                                Show <?php echo $comment['reply_count']; ?> replies
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 col-xl-3">
            <div class="sticky-sidebar" style="top: 100px;">
                <!-- Table of Contents -->
                <div class="sidebar-widget mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-list"></i> Table of Contents
                    </h5>
                    <div class="toc-container">
                        <ul class="toc-list" id="tableOfContents">
                            <!-- Generated by JavaScript -->
                        </ul>
                    </div>
                </div>

                <!-- About This Tutorial -->
                <div class="sidebar-widget mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle"></i> About This Tutorial
                    </h5>
                    <p class="small text-muted mb-3"><?php echo htmlspecialchars($postData['excerpt']); ?></p>
                    <div class="tutorial-stats">
                        <div class="stat-item">
                            <i class="fas fa-clock text-primary"></i>
                            <span><?php echo $readingTime; ?> min read</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-eye text-info"></i>
                            <span><?php echo number_format($postData['view_count'] ?? 0); ?> views</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-comments text-success"></i>
                            <span><?php echo count($comments); ?> comments</span>
                        </div>
                    </div>
                    <div class="tutorial-badges mt-3">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($postData['category_name']); ?></span>
                        <?php if ($postData['is_premium']): ?>
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-crown me-1"></i>Premium
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Newsletter Sidebar Widget -->
                <div class="sidebar-widget mb-4">
                    <h6 class="mb-3 text-primary">
                        <i class="fas fa-envelope me-2"></i>Stay Updated
                    </h6>
                    <p class="small text-muted mb-3">Get notified about new tutorials and exclusive content.</p>
                    <div class="newsletter-form">
                        <input type="email" class="form-control mb-2" id="sidebarNewsletterEmail" placeholder="Your email address">
                        <button class="btn btn-primary btn-sm w-100" type="button" id="sidebarNewsletterSubmit">
                            <i class="fas fa-paper-plane me-1"></i>Subscribe
                        </button>
                        <div id="sidebarNewsletterMessage" class="alert mt-2" style="display: none;"></div>
                    </div>
                </div>

                <!-- Related Posts -->
                <div class="sidebar-widget mb-4">
                    <h6 class="mb-3 text-primary">
                        <i class="fas fa-bookmark me-2"></i>Related Tutorials
                    </h6>
                    <div class="related-posts">
                        <div class="related-post-item">
                            <a href="#" class="related-post-link">
                                <h6 class="related-post-title">Getting Started with Web Development</h6>
                                <small class="text-muted">5 min read</small>
                            </a>
                        </div>
                        <div class="related-post-item">
                            <a href="#" class="related-post-link">
                                <h6 class="related-post-title">Advanced CSS Techniques</h6>
                                <small class="text-muted">8 min read</small>
                            </a>
                        </div>
                        <div class="related-post-item">
                            <a href="#" class="related-post-link">
                                <h6 class="related-post-title">JavaScript Best Practices</h6>
                                <small class="text-muted">6 min read</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Newsletter Section -->
<section class="newsletter-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="newsletter-content">
                    <h3 class="mb-4">Enjoyed This Tutorial?</h3>
                    <p class="text-muted mb-4">Subscribe to our newsletter and get more tutorials like this delivered to your inbox.</p>
                    <div class="newsletter-form">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" id="newsletterEmail" placeholder="Enter your email address">
                            <button class="btn btn-primary" type="button" id="newsletterSubmit">
                                <i class="fas fa-paper-plane me-1"></i>Subscribe
                            </button>
                        </div>
                        <div id="newsletterMessage" class="alert" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/modals.php'; ?>
<?php include 'includes/scripts.php'; ?>

<script>
// Post Page Enhancements
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== POST PAGE DEBUGGING START ===');
    console.log('DOM Content Loaded - Starting post page enhancements...');
    
    // Check if required functions exist
    console.log('Available functions:');
    console.log('- initializeReadingProgressBar:', typeof initializeReadingProgressBar);
    console.log('- generateTableOfContents:', typeof generateTableOfContents);
    console.log('- initializePostActions:', typeof initializePostActions);
    console.log('- initializeCommentEnhancements:', typeof initializeCommentEnhancements);
    console.log('- initializeVoting:', typeof initializeVoting);
    console.log('- initializeBookmarks:', typeof initializeBookmarks);
    
    // Check global variables
    console.log('Global variables:');
    console.log('- isLoggedIn:', typeof isLoggedIn !== 'undefined' ? isLoggedIn : 'undefined');
    console.log('- currentUser:', typeof currentUser !== 'undefined' ? currentUser : 'undefined');
    
    // Check if vote buttons exist
    const voteButtons = document.querySelectorAll('.vote-btn');
    console.log('Vote buttons found:', voteButtons.length);
    voteButtons.forEach((btn, index) => {
        console.log(`Vote button ${index}:`, {
            postId: btn.dataset.postId,
            voteType: btn.dataset.voteType,
            currentVote: btn.dataset.currentVote,
            element: btn
        });
    });
    
    // Check if bookmark buttons exist
    const bookmarkButtons = document.querySelectorAll('.bookmark-btn');
    console.log('Bookmark buttons found:', bookmarkButtons.length);
    bookmarkButtons.forEach((btn, index) => {
        console.log(`Bookmark button ${index}:`, {
            postId: btn.dataset.postId,
            element: btn
        });
    });
    
    // Test click events manually
    console.log('Testing manual click event binding...');
    voteButtons.forEach((btn, index) => {
        btn.addEventListener('click', function(e) {
            console.log(`Manual click test - Button ${index} clicked!`, {
                postId: this.dataset.postId,
                voteType: this.dataset.voteType,
                currentVote: this.dataset.currentVote
            });
        });
    });
    
    // Initialize all functionality
    console.log('Initializing functions...');
    
    // Set global user login status
    window.isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    console.log('User login status:', window.isLoggedIn);
    
    initializeReadingProgressBar();
    generateTableOfContents();
    initializePostActions();
    initializeCommentEnhancements();
    enhanceCommentForm();
    initializeLazyLoading();
    addPrintStyles();
    initializeImageLightbox();
    
    // Initialize enhanced post content features
    console.log('Initializing enhanced post content features...');
    addSmoothScrollingToHeadings();
    addCopyButtonsToCodeBlocks();
    
    // Set up reading progress updates
    window.addEventListener('scroll', updateReadingProgress);
    updateReadingProgress(); // Initial call
    
    // Initialize course progress tracking if user is logged in
    <?php if ($isLoggedIn): ?>
    initProgressTracking();
    <?php endif; ?>
    
    // Initialize voting and bookmark functionality
    console.log('Initializing voting and bookmark functionality...');
    
    if (typeof initializeVoting === 'function') {
        console.log('✓ initializeVoting function found, calling it...');
        try {
            initializeVoting();
            console.log('✓ initializeVoting called successfully');
        } catch (error) {
            console.error('❌ Error calling initializeVoting:', error);
        }
    } else {
        console.warn('❌ initializeVoting function not found');
    }
    
    if (typeof initializeBookmarks === 'function') {
        console.log('✓ initializeBookmarks function found, calling it...');
        try {
            initializeBookmarks();
            console.log('✓ initializeBookmarks called successfully');
        } catch (error) {
            console.error('❌ Error calling initializeBookmarks:', error);
        }
    } else {
        console.warn('❌ initializeBookmarks function not found');
    }
    
    // Check if user is logged in
    if (typeof isLoggedIn !== 'undefined') {
        console.log('User login status:', isLoggedIn);
    } else {
        console.warn('isLoggedIn variable not found');
    }
    
    // Initialize keyboard shortcuts
    initializeKeyboardShortcuts();
    
    console.log('=== POST PAGE DEBUGGING END ===');
});

// Reading Progress Bar
function initializeReadingProgressBar() {
    const progressBar = document.getElementById('readingProgressBar');
    if (!progressBar) return;

    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        progressBar.style.width = scrollPercent + '%';
    });
}

// Generate Table of Contents
function generateTableOfContents() {
    const tocContainer = document.getElementById('tableOfContents');
    if (!tocContainer) return;

    const headings = document.querySelectorAll('.post-content h1, .post-content h2, .post-content h3, .post-content h4, .post-content h5, .post-content h6');
    
    if (headings.length === 0) {
        tocContainer.innerHTML = '<li><span class="text-muted">No headings found</span></li>';
        return;
    }

    const tocList = document.createElement('ul');
    tocList.className = 'toc-list';

    headings.forEach((heading, index) => {
        // Add ID to heading if it doesn't have one
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }

        const listItem = document.createElement('li');
        const link = document.createElement('a');
        link.href = '#' + heading.id;
        link.textContent = heading.textContent;
        link.className = 'toc-link';
        
        // Add indentation based on heading level
        const level = parseInt(heading.tagName.charAt(1));
        link.style.paddingLeft = (level - 1) * 20 + 'px';
        
        listItem.appendChild(link);
        tocList.appendChild(listItem);
    });

    tocContainer.appendChild(tocList);

    // Smooth scrolling for TOC links
    tocContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('toc-link')) {
            e.preventDefault();
            const targetId = e.target.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const headerHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = targetElement.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });

    // Highlight active TOC item while scrolling
    window.addEventListener('scroll', function() {
        highlightActiveTOCItem();
    });
}

// Highlight active TOC item
function highlightActiveTOCItem() {
    const headings = document.querySelectorAll('.post-content h1, .post-content h2, .post-content h3, .post-content h4, .post-content h5, .post-content h6');
    const tocLinks = document.querySelectorAll('.toc-link');
    
    if (headings.length === 0 || tocLinks.length === 0) return;

    const scrollPosition = window.pageYOffset + 100;
    
    let currentHeading = null;
    headings.forEach(heading => {
        if (heading.offsetTop <= scrollPosition) {
            currentHeading = heading;
        }
    });

    tocLinks.forEach(link => {
        link.classList.remove('active');
        if (currentHeading && link.getAttribute('href') === '#' + currentHeading.id) {
            link.classList.add('active');
        }
    });
}

// Initialize Post Actions
function initializePostActions() {
    // Share functionality
    if (typeof navigator.share !== 'undefined') {
        const shareBtn = document.querySelector('[onclick="sharePost()"]');
        if (shareBtn) {
            shareBtn.onclick = sharePost;
        }
    }
}

// Add smooth scrolling to headings
function addSmoothScrollingToHeadings() {
    const headings = document.querySelectorAll('.post-content h1, .post-content h2, .post-content h3, .post-content h4, .post-content h5, .post-content h6');
    
    headings.forEach(heading => {
        // Add click handler for smooth scrolling
        heading.style.cursor = 'pointer';
        heading.addEventListener('click', () => {
            heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        
        // Add hover effect
        heading.addEventListener('mouseenter', () => {
            heading.style.color = '#4299e1';
        });
        
        heading.addEventListener('mouseleave', () => {
            heading.style.color = '';
        });
    });
}

// Add copy buttons to code blocks
function addCopyButtonsToCodeBlocks() {
    // First, handle traditional pre elements (standalone)
    const standalonePreBlocks = document.querySelectorAll('.post-content pre:not(.code-block pre)');
    
    standalonePreBlocks.forEach((block, index) => {
        if (block.querySelector('.copy-btn')) return; // Already has copy button
        
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-btn btn btn-sm btn-outline-light position-absolute';
        copyBtn.style.cssText = 'top: 0.5rem; right: 0.5rem; z-index: 10; opacity: 0; transition: opacity 0.2s ease;';
        copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
        copyBtn.title = 'Copy code';
        
        block.style.position = 'relative';
        
        block.addEventListener('mouseenter', () => {
            copyBtn.style.opacity = '1';
        });
        
        block.addEventListener('mouseleave', () => {
            copyBtn.style.opacity = '0';
        });
        
        copyBtn.addEventListener('click', () => {
            const code = block.querySelector('code');
            if (code) {
                copyCodeToClipboard(code, copyBtn);
            }
        });
        
        block.appendChild(copyBtn);
    });
    
    // Then, handle admin-created code blocks (div.code-block)
    const adminCodeBlocks = document.querySelectorAll('.post-content .code-block');
    
    adminCodeBlocks.forEach((block, index) => {
        if (block.querySelector('.copy-btn')) return; // Already has copy button
        
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-btn btn btn-sm btn-outline-light position-absolute';
        copyBtn.style.cssText = 'top: 0.5rem; right: 0.5rem; z-index: 10; opacity: 0; transition: opacity 0.2s ease;';
        copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
        copyBtn.title = 'Copy code';
        
        // Ensure the block has relative positioning
        if (!block.style.position || block.style.position === 'static') {
            block.style.position = 'relative';
        }
        
        block.addEventListener('mouseenter', () => {
            copyBtn.style.opacity = '1';
        });
        
        block.addEventListener('mouseleave', () => {
            copyBtn.style.opacity = '0';
        });
        
        copyBtn.addEventListener('click', () => {
            // For admin-created code blocks, find the pre > code
            const preElement = block.querySelector('pre');
            if (preElement) {
                const code = preElement.querySelector('code');
                if (code) {
                    copyCodeToClipboard(code, copyBtn);
                }
            }
        });
        
        block.appendChild(copyBtn);
    });
}

// Helper function to copy code to clipboard
function copyCodeToClipboard(code, copyBtn) {
    navigator.clipboard.writeText(code.textContent).then(() => {
        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
        copyBtn.className = 'copy-btn btn btn-sm btn-success position-absolute';
        setTimeout(() => {
            copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
            copyBtn.className = 'copy-btn btn btn-sm btn-outline-light position-absolute';
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = code.textContent;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        textArea.remove();
        
        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
        copyBtn.className = 'copy-btn btn btn-sm btn-success position-absolute';
        setTimeout(() => {
            copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
            copyBtn.className = 'copy-btn btn btn-sm btn-outline-light position-absolute';
        }, 2000);
    });
}

// Update reading progress
function updateReadingProgress() {
    const content = document.querySelector('.post-content');
    const progressBar = document.getElementById('readingProgressBar');
    
    if (!content || !progressBar) return;
    
    const contentHeight = content.offsetHeight;
    const windowHeight = window.innerHeight;
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const contentTop = content.offsetTop;
    
    if (scrollTop < contentTop) {
        progressBar.style.width = '0%';
        return;
    }
    
    const scrollProgress = Math.min((scrollTop - contentTop) / (contentHeight - windowHeight), 1);
    progressBar.style.width = `${scrollProgress * 100}%`;
}

// Share Post
function sharePost() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: document.querySelector('.post-excerpt')?.textContent || '',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        copyPostLink();
    }
}

// Copy Post Link
function copyPostLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        showToast('Link copied to clipboard!', 'success');
    }).catch(function() {
        showToast('Failed to copy link', 'error');
    });
}

// Initialize Comment Enhancements
function initializeCommentEnhancements() {
    console.log('Initializing comment enhancements...');
    
    // Use event delegation for reply buttons
    document.addEventListener('click', function(e) {
        // Reply button click
        if (e.target.closest('.reply-btn')) {
            e.preventDefault();
            const button = e.target.closest('.reply-btn');
            const commentId = button.getAttribute('data-comment-id');
            console.log('Reply button clicked for comment:', commentId);
            toggleReplyForm(commentId);
        }
        
        // Cancel reply button click
        if (e.target.closest('.cancel-reply-btn')) {
            e.preventDefault();
            const button = e.target.closest('.cancel-reply-btn');
            const commentId = button.getAttribute('data-comment-id');
            console.log('Cancel reply button clicked for comment:', commentId);
            toggleReplyForm(commentId);
            return;
        }
        
        // Show replies button click
        if (e.target.closest('.show-replies-btn')) {
            e.preventDefault();
            const button = e.target.closest('.show-replies-btn');
            const commentId = button.getAttribute('data-comment-id');
            console.log('Show replies button clicked for comment:', commentId);
            toggleReplies(commentId);
        }
    });

    // Initialize character counters
    initializeReplyFormCounters();
    
    // Initialize reply forms
    initializeReplyForms();
    
    console.log('Comment enhancements initialized');
}

// Toggle Reply Form
function toggleReplyForm(commentId) {
    console.log('toggleReplyForm called with commentId:', commentId);
    const replyForm = document.getElementById(`replyForm_${commentId}`);
    console.log('Found reply form:', replyForm);
    
    if (replyForm) {
        const isVisible = replyForm.style.display !== 'none';
        console.log('Form is currently visible:', isVisible);
        replyForm.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible) {
            const textarea = replyForm.querySelector('.reply-textarea');
            if (textarea) {
                textarea.focus();
                console.log('Focused on reply textarea');
            }
        }
    } else {
        console.error('Reply form not found for comment ID:', commentId);
    }
}

// Toggle Replies Visibility
function toggleReplies(commentId) {
    const repliesContainer = document.getElementById(`replies_${commentId}`);
    const showButton = document.querySelector(`[data-comment-id="${commentId}"].show-replies-btn`);
    
    if (repliesContainer && showButton) {
        const isVisible = repliesContainer.style.display !== 'none';
        repliesContainer.style.display = isVisible ? 'none' : 'block';
        
        if (isVisible) {
            // Extract the reply count from the button text
            const replyCount = showButton.textContent.match(/\d+/);
            if (replyCount) {
                showButton.innerHTML = `<i class="fas fa-chevron-down"></i> Show ${replyCount[0]} replies`;
            }
        } else {
            showButton.innerHTML = `<i class="fas fa-chevron-up"></i> Hide replies`;
        }
    }
}

// Initialize Reply Form Character Counters
function initializeReplyFormCounters() {
    // Reply form counters
    const replyTextareas = document.querySelectorAll('.reply-textarea');
    console.log('Found reply textareas:', replyTextareas.length);
    
    replyTextareas.forEach((textarea, index) => {
        const charCounter = textarea.parentElement.querySelector('.reply-char-count');
        if (charCounter) {
            console.log(`Initializing character counter ${index} for reply textarea`);
            textarea.addEventListener('input', function() {
                const remaining = 1000 - this.value.length;
                charCounter.textContent = this.value.length;
                
                if (remaining < 100) {
                    charCounter.style.color = remaining < 50 ? '#dc3545' : '#ffc107';
                } else {
                    charCounter.style.color = '';
                }
            });
        } else {
            console.warn(`Character counter not found for reply textarea ${index}`);
        }
    });
}

// Initialize Reply Forms
function initializeReplyForms() {
    const replyForms = document.querySelectorAll('.reply-form');
    console.log('Found reply forms:', replyForms.length);
    
    replyForms.forEach((form, index) => {
        const commentId = form.getAttribute('data-comment-id');
        console.log(`Initializing reply form ${index} for comment ${commentId}`);
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const commentId = this.getAttribute('data-comment-id');
            const textarea = this.querySelector('.reply-textarea');
            const content = textarea.value.trim();
            
            if (content) {
                submitReply(commentId, content, this);
            }
        });
    });
}

// Submit Reply
function submitReply(commentId, content, formElement) {
    const submitBtn = formElement.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
    
    // Prepare data
    const data = {
        comment_id: commentId,
        content: content
    };
    
    // Add guest info if not logged in
    if (!window.isLoggedIn) {
        const guestName = prompt('Please enter your name:');
        if (!guestName) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        data.guest_name = guestName;
    }
    
    // Send request
            fetch('api/replies', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success message
            showToast('Reply posted successfully!', 'success');
            
            // Clear form and hide it
            formElement.querySelector('.reply-textarea').value = '';
            toggleReplyForm(commentId);
            
            // Reload page to show new reply
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast(result.message || 'Failed to post reply', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while posting reply', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Show Toast Notification
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    // Set background color based on type
    switch(type) {
        case 'success':
            toast.style.backgroundColor = '#28a745';
            break;
        case 'error':
            toast.style.backgroundColor = '#dc3545';
            break;
        case 'warning':
            toast.style.backgroundColor = '#ffc107';
            toast.style.color = '#212529';
            break;
        default:
            toast.style.backgroundColor = '#17a2b8';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Enhanced Comment Form
function enhanceCommentForm() {
    const commentForm = document.getElementById('commentForm');
    const commentTextarea = document.getElementById('commentContent');
    const charCount = document.getElementById('charCount');
    
    if (!commentForm || !commentTextarea) return;
    
    // Add character counter functionality
    function updateCharCounter() {
        const remaining = 1000 - commentTextarea.value.length;
        charCount.textContent = commentTextarea.value.length;
        
        if (remaining < 100) {
            charCount.style.color = '#dc3545';
        } else if (remaining < 200) {
            charCount.style.color = '#ffc107';
        } else {
            charCount.style.color = 'var(--text-muted)';
        }
    }
    
    commentTextarea.addEventListener('input', updateCharCounter);
    updateCharCounter();
    
    // Add submit button state management
    const submitBtn = commentForm.querySelector('button[type="submit"]');
    if (submitBtn) {
        commentTextarea.addEventListener('input', function() {
            submitBtn.disabled = this.value.trim().length === 0;
        });
    }
    
    // Add form submission handler
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitComment();
    });
}

// Submit Comment
function submitComment() {
    const commentForm = document.getElementById('commentForm');
    const commentTextarea = document.getElementById('commentContent');
    const submitBtn = commentForm.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('commentMessage');
    
    const content = commentTextarea.value.trim();
    const postId = document.getElementById('postId').value;
    
    if (!content) {
        showMessage('Please enter a comment', 'warning');
        return;
    }
    
    if (content.length > 1000) {
        showMessage('Comment cannot exceed 1000 characters', 'error');
        return;
    }
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
    
    // Prepare data
    const data = {
        post_id: postId,
        content: content
    };
    
    // Add guest info if not logged in
    if (!window.isLoggedIn) {
        const guestName = prompt('Please enter your name:');
        if (!guestName) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        data.guest_name = guestName;
        
        const guestEmail = prompt('Please enter your email (optional):');
        if (guestEmail) {
            data.guest_email = guestEmail;
        }
    }
    
    // Send request
            fetch('api/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success message
            showMessage('Comment posted successfully!', 'success');
            
            // Clear form
            commentTextarea.value = '';
            // Update character counter
            const charCount = document.getElementById('charCount');
            if (charCount) {
                charCount.textContent = '0';
                charCount.style.color = 'var(--text-muted)';
            }
            
            // Reload page to show new comment
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showMessage(result.message || 'Failed to post comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while posting comment', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Show Message
function showMessage(message, type = 'info') {
    const messageDiv = document.getElementById('commentMessage');
    if (!messageDiv) return;
    
    messageDiv.className = `alert alert-${type}`;
    messageDiv.textContent = message;
    messageDiv.style.display = 'block';
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}

// Keyboard Shortcuts
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit comment
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.id === 'commentContent') {
                e.preventDefault();
                const commentForm = document.getElementById('commentForm');
                if (commentForm) {
                    commentForm.dispatchEvent(new Event('submit'));
                }
            }
        }
        
        // Escape to clear comment form
        if (e.key === 'Escape') {
            const commentTextarea = document.getElementById('commentContent');
            if (commentTextarea && document.activeElement === commentTextarea) {
                commentTextarea.value = '';
                commentTextarea.blur();
            }
        }
    });
}

// Lazy Loading for Images
function initializeLazyLoading() {
    const images = document.querySelectorAll('.post-content img');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => {
            if (img.dataset.src) {
                img.classList.add('lazy');
                imageObserver.observe(img);
            }
        });
    }
}


// Enhanced Post Content Features
function initializePostContentFeatures() {
    const content = document.querySelector('.post-content');
    if (!content) return;
    
    // Initialize image lightbox
    initializeImageLightbox();
    
    // Add smooth scrolling to headings
    addSmoothScrollingToHeadings();
    
    // Add table of contents
    addTableOfContents();
    
    // Add copy buttons to code blocks
    addCopyButtonsToCodeBlocks();
    
    // Add reading progress indicator
    updateReadingProgress();
}

// Image Lightbox for Post Content
function initializeImageLightbox() {
    const content = document.querySelector('.post-content');
    if (!content) return;

    // Create modal once
    let modal = document.getElementById('imageLightboxModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'imageLightboxModal';
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-transparent border-0">
                    <button type="button" class="btn-close btn-close-white ms-auto me-2 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="modal-body p-0 text-center">
                        <img id="lightboxImage" src="" alt="" class="img-fluid rounded shadow" style="max-height: 80vh;">
                        <div id="lightboxCaption" class="text-light mt-2 small"></div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
    }

    const bsModal = new bootstrap.Modal(modal);
    const lightboxImg = modal.querySelector('#lightboxImage');
    const lightboxCaption = modal.querySelector('#lightboxCaption');

    content.querySelectorAll('img').forEach(img => {
        // Skip images that explicitly opt-out
        if (img.dataset.noLightbox === 'true') return;
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', () => {
            lightboxImg.src = img.currentSrc || img.src;
            lightboxImg.alt = img.alt || '';
            // If inside a figure with figcaption, use that as caption
            const figure = img.closest('figure');
            const figcaption = figure ? figure.querySelector('figcaption') : null;
            lightboxCaption.textContent = figcaption ? figcaption.textContent : (img.alt || '');
            bsModal.show();
        });
    });
}



// Print Styles
function addPrintStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            .navbar, .sidebar, .voting-section, .comments-section, .newsletter-section,
            .breadcrumb-nav, .post-actions, .reading-progress-bar {
                display: none !important;
            }
            
            .post-header, .post-content {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .post-title {
                font-size: 2rem !important;
                color: black !important;
                -webkit-text-fill-color: black !important;
            }
            
            .post-content {
                font-size: 1rem !important;
                line-height: 1.6 !important;
            }
            
            body {
                padding: 0 !important;
                margin: 1in !important;
            }
        }
    `;
    document.head.appendChild(style);
}

// Course Progress Tracking
function initProgressTracking() {
    const postId = <?php echo $postData['id']; ?>;
    const courseModuleId = <?php echo $postData['course_module_id'] ?? 'null'; ?>;
    
    if (!courseModuleId) {
        return; // Not part of a course
    }
    
    let startTime = Date.now();
    let progressPercentage = 0;
    let trackingInterval;
    
    // Track when user starts reading
    trackProgress('start_lesson', postId, 0, 0);
    
    // Track progress based on scroll position
    function updateProgress() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const currentProgress = Math.min(Math.round((scrollTop / scrollHeight) * 100), 100);
        
        // Update floating progress indicator
        updateFloatingProgress(currentProgress);
        
        if (currentProgress > progressPercentage && currentProgress >= 25) {
            progressPercentage = currentProgress;
            const timeSpent = Math.round((Date.now() - startTime) / (1000 * 60)); // Convert to minutes
            trackProgress('update_progress', postId, progressPercentage, timeSpent);
        }
        
        // Auto-complete when user reaches 90% or more
        if (currentProgress >= 90) {
            const timeSpent = Math.round((Date.now() - startTime) / (1000 * 60));
            trackProgress('complete_lesson', postId, 100, timeSpent);
            clearInterval(trackingInterval);
            showCompletionMessage();
        }
    }
    
    // Track progress every few seconds
    trackingInterval = setInterval(updateProgress, 3000);
    
    // Also track on scroll for immediate feedback
    window.addEventListener('scroll', debounce(updateProgress, 1000));
    
    // Track when user leaves the page
    window.addEventListener('beforeunload', function() {
        const timeSpent = Math.round((Date.now() - startTime) / (1000 * 60));
        if (progressPercentage > 0) {
            trackProgress('update_progress', postId, progressPercentage, timeSpent);
        }
    });
}

function trackProgress(action, postId, progressPercentage, timeSpent) {
    if (!postId) return;
    
    fetch('api/track-progress', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: action,
            post_id: postId,
            progress_percentage: progressPercentage,
            time_spent: timeSpent
        })
    }).catch(error => {
        console.error('Progress tracking error:', error);
    });
}

function showCompletionMessage() {
    // Create completion toast
    const toast = document.createElement('div');
    toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 400px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <div>
                <strong>Lesson Completed!</strong>
                <br><small>Great job! Your progress has been saved.</small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Update floating progress indicator
function updateFloatingProgress(progress) {
    const floatingProgress = document.getElementById('floatingProgress');
    if (!floatingProgress) return;
    
    // Update progress number
    const progressNumber = floatingProgress.querySelector('.progress-number');
    if (progressNumber) {
        progressNumber.textContent = progress + '%';
    }
    
    // Update progress ring
    const progressRing = floatingProgress.querySelector('.progress-ring-fill');
    if (progressRing) {
        const circumference = 2 * Math.PI * 15.9155; // Based on SVG radius
        const offset = circumference - (progress / 100) * circumference;
        progressRing.style.strokeDashoffset = offset;
    }
    
    // Change color based on progress
    if (progress >= 90) {
        progressRing.style.stroke = '#28a745'; // Green for completion
        progressNumber.style.color = '#28a745';
    } else if (progress >= 50) {
        progressRing.style.stroke = '#ffc107'; // Yellow for halfway
        progressNumber.style.color = '#ffc107';
    } else {
        progressRing.style.stroke = '#007bff'; // Blue for early progress
        progressNumber.style.color = '#007bff';
    }
}

// Initialize floating progress on page load
document.addEventListener('DOMContentLoaded', function() {
    const floatingProgress = document.getElementById('floatingProgress');
    if (floatingProgress) {
        // Show the indicator after a short delay
        setTimeout(() => {
            floatingProgress.style.opacity = '1';
            floatingProgress.style.transform = 'scale(1)';
        }, 1000);
    }
});


</script> 