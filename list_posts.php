<?php
require_once 'config/session.php';
require_once 'includes/Post.php';

echo "<h1>Available Posts</h1>";

try {
    $post = new Post();
    $posts = $post->getAllPosts(20); // Get up to 20 posts
    
    if (!empty($posts)) {
        echo "<p>Found " . count($posts) . " posts:</p>";
        echo "<ul>";
        foreach ($posts as $postData) {
            echo "<li>";
            echo "<strong>" . htmlspecialchars($postData['title']) . "</strong>";
            echo " - Slug: <code>" . htmlspecialchars($postData['slug']) . "</code>";
            echo " - Category: " . htmlspecialchars($postData['category_name']);
            echo " - Status: " . htmlspecialchars($postData['status']);
            echo " - <a href='post.php?slug=" . urlencode($postData['slug']) . "'>View Post</a>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No posts found in database.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
