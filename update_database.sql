-- Update existing database to add missing columns
-- Run this script if you have an existing database

USE telie_academy;

-- Add is_featured column to posts table
ALTER TABLE posts ADD COLUMN is_featured BOOLEAN DEFAULT FALSE AFTER is_premium;

-- Update some posts to be featured (optional)
UPDATE posts SET is_featured = TRUE WHERE id IN (1, 2, 3); 