-- Add views table for tracking post views
-- This upgrade adds support for post view counting

USE telie_academy;

-- Create views table
CREATE TABLE IF NOT EXISTS post_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_post_views_post_id ON post_views(post_id);
CREATE INDEX idx_post_views_viewed_at ON post_views(viewed_at);
CREATE INDEX idx_post_views_ip_post ON post_views(ip_address, post_id);

-- Add view_count column to posts table for caching
ALTER TABLE posts ADD COLUMN view_count INT DEFAULT 0;

-- Update existing posts to have 0 views
UPDATE posts SET view_count = 0 WHERE view_count IS NULL;

