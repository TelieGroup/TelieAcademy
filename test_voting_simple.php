<?php
require_once 'config/session.php';
require_once 'includes/Post.php';
require_once 'includes/User.php';
require_once 'includes/Vote.php';

echo "<h1>Simple Voting Test</h1>";

try {
    $post = new Post();
    $user = new User();
    $vote = new Vote();
    
    echo "<p>✓ Classes loaded successfully</p>";
    
    // Check if user is logged in
    $isLoggedIn = $user->isLoggedIn();
    echo "<p>User logged in: " . ($isLoggedIn ? 'Yes' : 'No') . "</p>";
    
    if ($isLoggedIn) {
        $currentUser = $user->getCurrentUser();
        echo "<p>Current user: " . htmlspecialchars($currentUser['username']) . " (ID: " . $currentUser['id'] . ")</p>";
    }
    
    // Get a sample post
    $posts = $post->getAllPosts(1);
    if (!empty($posts)) {
        $postData = $posts[0];
        echo "<p>✓ Sample post found: " . htmlspecialchars($postData['title']) . " (ID: " . $postData['id'] . ")</p>";
        
        // Test vote statistics
        $voteStats = $vote->getPostVoteStats($postData['id']);
        echo "<p>✓ Vote stats retrieved: " . json_encode($voteStats) . "</p>";
        
        if ($isLoggedIn) {
            // Test if user can vote
            $canVote = $vote->canUserVote($postData['id'], $currentUser['id']);
            echo "<p>✓ Can user vote: " . ($canVote ? 'Yes' : 'No') . "</p>";
            
            // Test user's current vote
            $userVote = $vote->getUserVote($postData['id'], $currentUser['id']);
            echo "<p>✓ User's current vote: " . ($userVote ? $userVote['vote_type'] : 'None') . "</p>";
        }
    } else {
        echo "<p>❌ No posts found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Test Voting API</h2>";
echo "<p>If you're logged in, you can test the voting API:</p>";
echo "<button onclick='testVote()'>Test Upvote</button>";
echo "<div id='voteResult'></div>";

echo "<script>";
echo "async function testVote() {";
echo "  const resultDiv = document.getElementById('voteResult');";
echo "  resultDiv.innerHTML = 'Testing...';";
echo "  try {";
echo "    const response = await fetch('api/vote.php', {";
echo "      method: 'POST',";
echo "      headers: { 'Content-Type': 'application/json' },";
echo "      body: JSON.stringify({ post_id: 1, vote_type: 'upvote' })";
echo "    });";
echo "    const data = await response.json();";
echo "    resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';";
echo "  } catch (error) {";
echo "    resultDiv.innerHTML = 'Error: ' + error.message;";
echo "  }";
echo "}";
echo "</script>";
?>
