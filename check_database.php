<?php
echo "<h1>Database Check</h1>";

try {
    require_once 'config/database.php';
    $db = getDB();
    echo "<p>✓ Database connection successful</p>";
    
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
    } else {
        echo "<p>Creating votes table...</p>";
        $createVotesTable = "
        CREATE TABLE IF NOT EXISTS `votes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `post_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `vote_type` enum('upvote','downvote') NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_user_post_vote` (`user_id`,`post_id`),
          KEY `post_id` (`post_id`),
          KEY `user_id` (`user_id`),
          KEY `vote_type` (`vote_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($createVotesTable);
        echo "<p>✓ Votes table created successfully</p>";
    }
    
    // Check if bookmarks table exists
    $stmt = $db->query("SHOW TABLES LIKE 'bookmarks'");
    $bookmarksTableExists = $stmt->rowCount() > 0;
    echo "<p>Bookmarks table exists: " . ($bookmarksTableExists ? 'Yes' : 'No') . "</p>";
    
    if (!$bookmarksTableExists) {
        echo "<p>Creating bookmarks table...</p>";
        $createBookmarksTable = "
        CREATE TABLE IF NOT EXISTS `bookmarks` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `post_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_user_post_bookmark` (`user_id`,`post_id`),
          KEY `user_id` (`user_id`),
          KEY `post_id` (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($createBookmarksTable);
        echo "<p>✓ Bookmarks table created successfully</p>";
    }
    
    // Check posts table for vote-related columns
    $stmt = $db->query("DESCRIBE posts");
    $postsStructure = $stmt->fetchAll();
    echo "<h3>Posts Table Structure:</h3>";
    echo "<pre>" . print_r($postsStructure, true) . "</pre>";
    
    // Check if posts table has vote-related columns
    $hasUpvotes = false;
    $hasDownvotes = false;
    $hasVoteScore = false;
    
    foreach ($postsStructure as $column) {
        if ($column['Field'] === 'upvotes') $hasUpvotes = true;
        if ($column['Field'] === 'downvotes') $hasDownvotes = true;
        if ($column['Field'] === 'vote_score') $hasVoteScore = true;
    }
    
    if (!$hasUpvotes || !$hasDownvotes || !$hasVoteScore) {
        echo "<p>Adding vote-related columns to posts table...</p>";
        
        if (!$hasUpvotes) {
            $db->exec("ALTER TABLE posts ADD COLUMN upvotes int(11) NOT NULL DEFAULT 0");
            echo "<p>✓ Added upvotes column</p>";
        }
        
        if (!$hasDownvotes) {
            $db->exec("ALTER TABLE posts ADD COLUMN downvotes int(11) NOT NULL DEFAULT 0");
            echo "<p>✓ Added downvotes column</p>";
        }
        
        if (!$hasVoteScore) {
            $db->exec("ALTER TABLE posts ADD COLUMN vote_score int(11) NOT NULL DEFAULT 0");
            echo "<p>✓ Added vote_score column</p>";
        }
    } else {
        echo "<p>✓ Posts table has all required vote columns</p>";
    }
    
    // Show sample data
    echo "<h3>Sample Posts:</h3>";
    $stmt = $db->query("SELECT id, title, upvotes, downvotes, vote_score FROM posts LIMIT 5");
    $posts = $stmt->fetchAll();
    echo "<pre>" . print_r($posts, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}
?>
