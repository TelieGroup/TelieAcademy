-- OAuth and Email Verification Database Upgrade (Fixed)
-- Run this script to add OAuth support and email verification to the users table

USE telie_academy;

-- Add OAuth and email verification fields to users table (only missing ones)
ALTER TABLE users 
ADD COLUMN oauth_provider ENUM('email', 'linkedin', 'google', 'github') DEFAULT 'email' AFTER email,
ADD COLUMN oauth_id VARCHAR(255) NULL AFTER oauth_provider,
ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER password_hash,
ADD COLUMN email_verification_token VARCHAR(255) NULL AFTER email_verified,
ADD COLUMN email_verification_expires TIMESTAMP NULL AFTER email_verification_token,
ADD COLUMN profile_picture VARCHAR(255) NULL AFTER email_verification_expires,
ADD COLUMN first_name VARCHAR(100) NULL AFTER profile_picture,
ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name,
ADD COLUMN location VARCHAR(100) NULL AFTER website,
ADD COLUMN company VARCHAR(100) NULL AFTER location,
ADD COLUMN job_title VARCHAR(100) NULL AFTER company,
ADD COLUMN linkedin_profile VARCHAR(255) NULL AFTER job_title,
ADD COLUMN github_profile VARCHAR(255) NULL AFTER linkedin_profile,
ADD COLUMN twitter_profile VARCHAR(255) NULL AFTER github_profile,
ADD COLUMN last_login TIMESTAMP NULL AFTER twitter_profile,
ADD COLUMN login_count INT DEFAULT 0 AFTER last_login,
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER login_count,
ADD COLUMN password_reset_token VARCHAR(255) NULL AFTER is_active,
ADD COLUMN password_reset_expires TIMESTAMP NULL AFTER password_reset_token;

-- Add indexes for better performance
CREATE INDEX idx_users_oauth ON users(oauth_provider, oauth_id);
CREATE INDEX idx_users_email_verified ON users(email_verified);
CREATE INDEX idx_users_active ON users(is_active);

-- Create OAuth tokens table for storing OAuth refresh tokens
CREATE TABLE IF NOT EXISTS oauth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider ENUM('linkedin', 'google', 'github') NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_provider (user_id, provider)
);

-- Create email verification logs table
CREATE TABLE IF NOT EXISTS email_verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create password reset logs table
CREATE TABLE IF NOT EXISTS password_reset_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Update existing users to have email_verified = TRUE (since they're already using the system)
UPDATE users SET email_verified = TRUE WHERE email_verified IS NULL;

-- Update the admin user to have proper OAuth settings
UPDATE users SET 
    oauth_provider = 'email',
    email_verified = TRUE,
    is_active = TRUE,
    first_name = 'Telie',
    last_name = 'Academy'
WHERE username = 'TelieAcademy'; 