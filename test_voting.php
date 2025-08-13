<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/User.php';
require_once 'includes/Vote.php';

$post = new Post();
$user = new User();
$vote = new Vote();

// Get a sample post
$posts = $post->getAllPosts(1);
$postData = $posts[0] ?? null;

if (!$postData) {
    die('No posts found in database');
}

$isLoggedIn = $user->isLoggedIn();
$isPremium = $isLoggedIn ? $user->getCurrentUser()['is_premium'] : false;

// Get vote statistics
$voteStats = $vote->getPostVoteStats($postData['id']);
$userVote = $isLoggedIn ? $vote->getUserVote($postData['id'], $user->getCurrentUser()['id']) : null;

echo "<h1>Voting Test Page</h1>";
echo "<p>Testing voting functionality for post: " . htmlspecialchars($postData['title']) . "</p>";
echo "<p>Post ID: " . $postData['id'] . "</p>";
echo "<p>User logged in: " . ($isLoggedIn ? 'Yes' : 'No') . "</p>";

if ($isLoggedIn) {
    $currentUser = $user->getCurrentUser();
    echo "<p>Current user ID: " . $currentUser['id'] . "</p>";
    echo "<p>Current user username: " . htmlspecialchars($currentUser['username']) . "</p>";
}

echo "<h2>Vote Statistics</h2>";
echo "<pre>" . print_r($voteStats, true) . "</pre>";

echo "<h2>User Vote</h2>";
echo "<pre>" . print_r($userVote, true) . "</pre>";

echo "<h2>Can User Vote?</h2>";
if ($isLoggedIn) {
    $canVote = $vote->canUserVote($postData['id'], $currentUser['id']);
    echo "<p>Can user vote: " . ($canVote ? 'Yes' : 'No') . "</p>";
}

echo "<h2>Database Tables Check</h2>";
try {
    $db = getDB();
    
    // Check if votes table exists
    $stmt = $db->query("SHOW TABLES LIKE 'votes'");
    $votesTableExists = $stmt->rowCount() > 0;
    echo "<p>Votes table exists: " . ($votesTableExists ? 'Yes' : 'No') . "</p>";
    
    if ($votesTableExists) {
        // Check votes table structure
        $stmt = $db->query("DESCRIBE votes");
        $votesStructure = $stmt->fetchAll();
        echo "<h3>Votes Table Structure:</h3>";
        echo "<pre>" . print_r($votesStructure, true) . "</pre>";
        
        // Check if there are any votes
        $stmt = $db->query("SELECT COUNT(*) as count FROM votes");
        $votesCount = $stmt->fetch();
        echo "<p>Total votes in database: " . $votesCount['count'] . "</p>";
    }
    
    // Check posts table for vote-related columns
    $stmt = $db->query("DESCRIBE posts");
    $postsStructure = $stmt->fetchAll();
    echo "<h3>Posts Table Structure:</h3>";
    echo "<pre>" . print_r($postsStructure, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}
?>
