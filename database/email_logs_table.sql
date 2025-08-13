-- Create email_logs table for tracking newsletter emails
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `type` enum('newsletter','notification','premium','test') NOT NULL DEFAULT 'newsletter',
  `sent_at` datetime NOT NULL,
  `status` enum('sent','failed','bounced') DEFAULT 'sent',
  `error_message` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subscriber_id` (`subscriber_id`),
  KEY `type` (`type`),
  KEY `sent_at` (`sent_at`),
  KEY `status` (`status`),
  CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `newsletter_subscribers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_email_logs_subscriber_type ON email_logs(subscriber_id, type);
CREATE INDEX idx_email_logs_sent_status ON email_logs(sent_at, status);
