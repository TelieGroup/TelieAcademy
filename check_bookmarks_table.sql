-- Check and create bookmarks table if it doesn't exist
USE telie_academy;

-- Check if bookmarks table exists
SELECT COUNT(*) as table_exists FROM information_schema.tables 
WHERE table_schema = 'telie_academy' AND table_name = 'bookmarks';

-- Create bookmarks table if it doesn't exist
CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_post_bookmark` (`user_id`,`post_id`),
  KEY `user_id` (`user_id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check bookmarks table structure
DESCRIBE bookmarks;

-- Show sample data
SELECT * FROM bookmarks LIMIT 5;
