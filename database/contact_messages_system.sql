-- Contact Messages System for TelieAcademy
-- This system allows users to send contact messages and admins to reply to them

-- Create contact_messages table
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL COMMENT 'ID of the user who sent the message (if logged in)',
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `subject` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `newsletter_subscribe` tinyint(1) DEFAULT 0 COMMENT 'Whether user wants to subscribe to newsletter',
    `status` enum('new','in_progress','replied','closed') DEFAULT 'new' COMMENT 'Message status',
    `priority` enum('low','medium','high','urgent') DEFAULT 'medium' COMMENT 'Message priority',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `admin_notes` text DEFAULT NULL COMMENT 'Internal admin notes',
    `assigned_to` int(11) DEFAULT NULL COMMENT 'Admin user ID assigned to handle this message',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_email` (`email`),
    KEY `idx_assigned_to` (`assigned_to`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create contact_replies table for admin replies
CREATE TABLE IF NOT EXISTS `contact_replies` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `message_id` int(11) NOT NULL COMMENT 'ID of the contact message being replied to',
    `admin_id` int(11) NOT NULL COMMENT 'ID of the admin who sent the reply',
    `reply_message` text NOT NULL,
    `is_internal` tinyint(1) DEFAULT 0 COMMENT 'Whether this is an internal note (not sent to user)',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_message_id` (`message_id`),
    KEY `idx_admin_id` (`admin_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`message_id`) REFERENCES `contact_messages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
INSERT INTO `contact_messages` (`first_name`, `last_name`, `email`, `phone`, `subject`, `message`, `newsletter_subscribe`, `status`, `priority`) VALUES
('John', 'Doe', 'john.doe@example.com', '+1234567890', 'General Inquiry', 'I have a question about your premium subscription plans. Can you provide more details?', 1, 'new', 'medium'),
('Jane', 'Smith', 'jane.smith@example.com', '+1987654321', 'Technical Support', 'I am unable to access the course materials. Getting an error message.', 0, 'in_progress', 'high'),
('Mike', 'Johnson', 'mike.johnson@example.com', NULL, 'Course Content', 'I would like to suggest adding more JavaScript tutorials to your course library.', 1, 'replied', 'low'),
('Sarah', 'Wilson', 'sarah.wilson@example.com', '+1555123456', 'Billing & Subscription', 'I was charged twice for my premium subscription this month.', 0, 'new', 'urgent'),
('David', 'Brown', 'david.brown@example.com', NULL, 'Partnership & Collaboration', 'I represent a coding bootcamp and would like to discuss potential collaboration opportunities.', 0, 'new', 'medium');

-- Insert sample admin replies
INSERT INTO `contact_replies` (`message_id`, `admin_id`, `reply_message`, `is_internal`) VALUES
(3, 1, 'Thank you for your suggestion! We are always looking to expand our course library. I have forwarded your request to our content team for consideration.', 0),
(3, 1, 'User seems interested in JavaScript content. Consider adding this to our content roadmap.', 1),
(2, 1, 'I have reset your account access. Please try logging in again. If the issue persists, please let me know what specific error message you are seeing.', 0);

-- Update the status of messages that have replies
UPDATE `contact_messages` SET `status` = 'replied' WHERE `id` IN (2, 3);
UPDATE `contact_messages` SET `assigned_to` = 1 WHERE `id` IN (2, 3);

-- Create indexes for better performance
CREATE INDEX `idx_contact_messages_status_priority` ON `contact_messages` (`status`, `priority`);
CREATE INDEX `idx_contact_messages_user_status` ON `contact_messages` (`user_id`, `status`);
CREATE INDEX `idx_contact_replies_message_admin` ON `contact_replies` (`message_id`, `admin_id`);

