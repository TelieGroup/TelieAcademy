-- Check and create votes table if it doesn't exist
USE telie_academy;

-- Check if votes table exists
SELECT COUNT(*) as table_exists FROM information_schema.tables 
WHERE table_schema = 'telie_academy' AND table_name = 'votes';

-- Create votes table if it doesn't exist
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
  KEY `vote_type` (`vote_type`),
  CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check if posts table has vote-related columns
DESCRIBE posts;

-- Add vote-related columns to posts table if they don't exist
ALTER TABLE `posts` 
ADD COLUMN IF NOT EXISTS `upvotes` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `downvotes` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `vote_score` int(11) NOT NULL DEFAULT 0;

-- Check votes table structure
DESCRIBE votes;

-- Show sample data
SELECT * FROM votes LIMIT 5;
SELECT id, title, upvotes, downvotes, vote_score FROM posts LIMIT 5;
