-- Create unsubscribe_feedback table for tracking unsubscribe reasons
CREATE TABLE IF NOT EXISTS `unsubscribe_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `feedback` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `reason` (`reason`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_unsubscribe_feedback_email_reason ON unsubscribe_feedback(email, reason);
CREATE INDEX idx_unsubscribe_feedback_created_reason ON unsubscribe_feedback(created_at, reason);
