-- Create votes table for post voting system
CREATE TABLE IF NOT EXISTS `votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` enum('upvote','downvote') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_post_vote` (`user_id`, `post_id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `vote_type` (`vote_type`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add vote count columns to posts table
ALTER TABLE `posts` 
ADD COLUMN `upvotes` int(11) NOT NULL DEFAULT 0 AFTER `is_featured`,
ADD COLUMN `downvotes` int(11) NOT NULL DEFAULT 0 AFTER `upvotes`,
ADD COLUMN `vote_score` int(11) NOT NULL DEFAULT 0 AFTER `downvotes`;

-- Create indexes for better performance
CREATE INDEX `idx_posts_vote_score` ON `posts` (`vote_score` DESC);
CREATE INDEX `idx_posts_upvotes` ON `posts` (`upvotes` DESC);
CREATE INDEX `idx_posts_created_votes` ON `posts` (`created_at` DESC, `vote_score` DESC);

-- Insert sample votes (optional)
INSERT INTO `votes` (`post_id`, `user_id`, `vote_type`) VALUES
(1, 1, 'upvote'),
(1, 2, 'upvote'),
(2, 1, 'upvote'),
(2, 2, 'downvote'),
(3, 1, 'upvote'),
(3, 2, 'upvote'),
(3, 3, 'upvote');

-- Update vote counts in posts table
UPDATE `posts` p SET 
p.upvotes = (SELECT COUNT(*) FROM `votes` v WHERE v.post_id = p.id AND v.vote_type = 'upvote'),
p.downvotes = (SELECT COUNT(*) FROM `votes` v WHERE v.post_id = p.id AND v.vote_type = 'downvote'),
p.vote_score = (SELECT COUNT(*) FROM `votes` v WHERE v.post_id = p.id AND v.vote_type = 'upvote') - 
               (SELECT COUNT(*) FROM `votes` v WHERE v.post_id = p.id AND v.vote_type = 'downvote'); 