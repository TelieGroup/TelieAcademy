<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/Comment.php';
require_once 'includes/Reply.php';
require_once 'includes/User.php';
require_once 'includes/Tag.php';
require_once 'includes/View.php';
require_once 'includes/Vote.php';

$post = new Post();
$comment = new Comment();
$reply = new Reply();
$user = new User();
$tag = new Tag();
$view = new View();
$vote = new Vote();

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Check if user is logged in and premium
$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

if (empty($slug)) {
    header('Location: index');
    exit;
}

$postData = $post->getPostBySlug($slug, $isPremium);

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
                <li class="breadcrumb-item"><a href="categories?category=<?php echo $postData['category_slug']; ?>"><?php echo htmlspecialchars($postData['category_name']); ?></a></li>
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

            <!-- Post Content -->
            <article class="post-content mb-5">
                <?php echo $renderedContent; ?>
            </article>

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


</script> 