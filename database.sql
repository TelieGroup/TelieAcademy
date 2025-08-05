-- TelieAcademy Database Schema
-- Tech Tutorial Blog Database

-- Create database
CREATE DATABASE IF NOT EXISTS telie_academy;
USE telie_academy;

-- Users table (for premium content and comments)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_premium BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tags table
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Posts table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255),
    author_id INT,
    category_id INT,
    is_premium BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published') DEFAULT 'published',
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Post tags relationship table
CREATE TABLE post_tags (
    post_id INT,
    tag_id INT,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Comments table
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT,
    guest_name VARCHAR(100),
    guest_email VARCHAR(100),
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Newsletter subscribers table
CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Affiliate products table
CREATE TABLE affiliate_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    affiliate_link VARCHAR(500),
    price DECIMAL(10,2),
    category VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Post affiliate products relationship table
CREATE TABLE post_affiliate_products (
    post_id INT,
    affiliate_product_id INT,
    position INT DEFAULT 0,
    PRIMARY KEY (post_id, affiliate_product_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (affiliate_product_id) REFERENCES affiliate_products(id) ON DELETE CASCADE
);

-- Ad placements table
CREATE TABLE ad_placements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    ad_code TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data

-- Sample users
INSERT INTO users (username, email, password_hash, is_premium) VALUES
('admin', 'admin@telieacademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Sample categories
INSERT INTO categories (name, slug, description) VALUES
('JavaScript', 'javascript', 'Modern JavaScript tutorials and guides'),
('React', 'react', 'React.js development tutorials'),
('Python', 'python', 'Python programming tutorials'),
('Web Development', 'web-development', 'General web development topics'),
('Data Science', 'data-science', 'Data science and analytics tutorials');

-- Sample tags
INSERT INTO tags (name, slug) VALUES
('javascript', 'javascript'),
('es6', 'es6'),
('modern-js', 'modern-js'),
('react', 'react'),
('components', 'components'),
('hooks', 'hooks'),
('python', 'python'),
('data-structures', 'data-structures'),
('algorithms', 'algorithms'),
('web-dev', 'web-dev'),
('frontend', 'frontend'),
('backend', 'backend');

-- Sample posts
INSERT INTO posts (title, slug, excerpt, content, author_id, category_id, is_premium, status) VALUES
('Getting Started with ES6+ Features', 'getting-started-with-es6-features', 'Learn the essential ES6+ features that every modern JavaScript developer should know.', '<h2>Introduction</h2><p>ES6+ (ECMAScript 2015 and later) introduced many powerful features that have become essential for modern JavaScript development...</p><h2>Arrow Functions</h2><p>Arrow functions provide a more concise syntax for writing function expressions...</p><h2>Template Literals</h2><p>Template literals allow you to embed expressions in string literals...</p>', 1, 1, FALSE, 'published'),
('Building Your First React Component', 'building-your-first-react-component', 'A step-by-step guide to creating your first React component with modern hooks.', '<h2>What is React?</h2><p>React is a JavaScript library for building user interfaces...</p><h2>Creating Your First Component</h2><p>Let\'s create a simple functional component...</p><h2>Using Hooks</h2><p>Hooks allow you to use state and other React features in functional components...</p>', 1, 2, FALSE, 'published'),
('Python Data Structures Explained', 'python-data-structures-explained', 'Master Python data structures with practical examples and use cases.', '<h2>Lists</h2><p>Lists are ordered, mutable collections in Python...</p><h2>Dictionaries</h2><p>Dictionaries store key-value pairs...</p><h2>Sets</h2><p>Sets are unordered collections of unique elements...</p>', 1, 3, TRUE, 'published'),
('Advanced JavaScript Patterns', 'advanced-javascript-patterns', 'Explore advanced JavaScript patterns and techniques for better code organization.', '<h2>Module Pattern</h2><p>The module pattern provides encapsulation...</p><h2>Observer Pattern</h2><p>The observer pattern allows objects to subscribe to events...</p>', 1, 1, TRUE, 'published'),
('React Hooks Deep Dive', 'react-hooks-deep-dive', 'A comprehensive guide to React hooks and their advanced usage patterns.', '<h2>useState Hook</h2><p>The useState hook allows functional components to have state...</p><h2>useEffect Hook</h2><p>The useEffect hook handles side effects in functional components...</p>', 1, 2, TRUE, 'published');

-- Link posts to tags
INSERT INTO post_tags (post_id, tag_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 10), -- JavaScript post tags
(2, 4), (2, 5), (2, 6), (2, 10), -- React post tags
(3, 7), (3, 8), (3, 9), (3, 12), -- Python post tags
(4, 1), (4, 3), (4, 10), -- Advanced JS post tags
(5, 4), (5, 6), (5, 10); -- React Hooks post tags

-- Sample affiliate products
INSERT INTO affiliate_products (name, description, image_url, affiliate_link, price, category) VALUES
('Eloquent JavaScript', 'A modern introduction to programming with JavaScript', '/images/books/eloquent-javascript.jpg', 'https://amzn.to/example1', 29.99, 'Books'),
('React: Up & Running', 'Build web applications with React', '/images/books/react-up-running.jpg', 'https://amzn.to/example2', 34.99, 'Books'),
('Python Crash Course', 'A hands-on introduction to Python programming', '/images/books/python-crash-course.jpg', 'https://amzn.to/example3', 39.99, 'Books'),
('VS Code Pro', 'Professional code editor with advanced features', '/images/tools/vscode-pro.jpg', 'https://example.com/vscode', 0.00, 'Tools'),
('GitHub Pro', 'Advanced features for GitHub repositories', '/images/tools/github-pro.jpg', 'https://github.com/pro', 4.99, 'Tools');

-- Link affiliate products to posts
INSERT INTO post_affiliate_products (post_id, affiliate_product_id, position) VALUES
(1, 1, 1), -- Eloquent JavaScript for JS post
(2, 2, 1), -- React book for React post
(3, 3, 1), -- Python book for Python post
(1, 4, 2), -- VS Code for JS post
(2, 4, 2), -- VS Code for React post
(3, 4, 2); -- VS Code for Python post

-- Sample ad placements
INSERT INTO ad_placements (name, location, ad_code, is_active) VALUES
('Homepage Banner', 'homepage', '<div class="ad-banner" id="homepage-banner-ad"><!-- AdSense code here --></div>', TRUE),
('Categories Banner', 'categories', '<div class="ad-banner" id="categories-banner-ad"><!-- AdSense code here --></div>', TRUE),
('Tags Banner', 'tags', '<div class="ad-banner" id="tags-banner-ad"><!-- AdSense code here --></div>', TRUE),
('Post In-Content', 'post-content', '<div class="ad-banner" id="post-content-ad"><!-- AdSense code here --></div>', TRUE),
('Post Footer', 'post-footer', '<div class="ad-banner" id="post-footer-ad"><!-- AdSense code here --></div>', TRUE);

-- Sample comments
INSERT INTO comments (post_id, user_id, content, status) VALUES
(1, 2, 'Great tutorial! The arrow functions section was very helpful.', 'approved'),
(1, NULL, 'guest_name', 'guest_email', 'This helped me understand ES6 much better. Thanks!', 'approved'),
(2, 3, 'Excellent guide for React beginners. Clear and concise.', 'approved'),
(3, 2, 'The data structures examples are very practical.', 'approved');

-- Sample newsletter subscribers
INSERT INTO newsletter_subscribers (email) VALUES
('subscriber1@example.com'),
('subscriber2@example.com'),
('subscriber3@example.com'); 