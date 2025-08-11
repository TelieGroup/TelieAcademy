<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/Post.php';
require_once '../includes/Category.php';
require_once '../includes/Tag.php';

try {
    $db = getDB();
    $post = new Post();
    $category = new Category();
    $tag = new Tag();
    
    $query = $_GET['q'] ?? '';
    $query = trim($query);
    
    if (strlen($query) < 2) {
        echo json_encode([
            'success' => false,
            'message' => 'Query too short',
            'suggestions' => []
        ]);
        exit;
    }
    
    $suggestions = [];
    
    // Get post suggestions
    $postSuggestions = $post->searchPosts($query, true, 5);
    foreach ($postSuggestions as $post) {
        $suggestions[] = [
            'type' => 'post',
            'title' => $post['title'],
            'url' => 'post.php?slug=' . $post['slug'],
            'category' => $post['category_name'],
            'icon' => 'fas fa-file-alt'
        ];
    }
    
    // Get category suggestions
    $categories = $category->getAllCategories();
    foreach ($categories as $cat) {
        if (stripos($cat['name'], $query) !== false || stripos($cat['description'], $query) !== false) {
            $suggestions[] = [
                'type' => 'category',
                'title' => $cat['name'],
                'url' => 'categories.php?category=' . $cat['slug'],
                'description' => $cat['description'],
                'icon' => 'fas fa-folder'
            ];
        }
    }
    
    // Get tag suggestions
    $tags = $tag->getPopularTags(20);
    foreach ($tags as $tagItem) {
        if (stripos($tagItem['name'], $query) !== false) {
            $suggestions[] = [
                'type' => 'tag',
                'title' => $tagItem['name'],
                'url' => 'tags.php?tag=' . $tagItem['slug'],
                'icon' => 'fas fa-tag'
            ];
        }
    }
    
    // Limit suggestions and sort by relevance
    $suggestions = array_slice($suggestions, 0, 10);
    
    echo json_encode([
        'success' => true,
        'query' => $query,
        'suggestions' => $suggestions,
        'total' => count($suggestions)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching suggestions: ' . $e->getMessage(),
        'suggestions' => []
    ]);
}
?> 