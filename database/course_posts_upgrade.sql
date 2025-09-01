-- Course Posts Integration
-- This script adds the ability to organize posts into course modules for progressive learning

USE telie_academy;

-- Add course_module_id to posts table to link posts to course modules
ALTER TABLE posts 
ADD COLUMN course_module_id INT DEFAULT NULL AFTER category_id,
ADD COLUMN lesson_order INT DEFAULT 0 AFTER course_module_id,
ADD CONSTRAINT fk_posts_course_module 
    FOREIGN KEY (course_module_id) REFERENCES course_modules(id) ON DELETE SET NULL;

-- Create course_progress table to track user progress through courses
CREATE TABLE IF NOT EXISTS course_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    module_id INT NOT NULL,
    post_id INT NOT NULL,
    completed_at TIMESTAMP NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    time_spent_minutes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_post_progress (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Create course_enrollments table to track which users are enrolled in courses
CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_user_course (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Insert sample course for JavaScript Learning Journey
INSERT INTO courses (title, slug, description, is_active) VALUES
('JavaScript Learning Journey', 'javascript-learning-journey', 'Complete JavaScript course from beginner to advanced with hands-on practice and real-world projects.', 1)
ON DUPLICATE KEY UPDATE title=title;

-- Insert modules for JavaScript course
SET @js_course_id = (SELECT id FROM courses WHERE slug = 'javascript-learning-journey');

INSERT INTO course_modules (course_id, title, slug, description, order_index, is_active) VALUES
(@js_course_id, 'JavaScript Fundamentals', 'javascript-fundamentals', 'Learn the building blocks of JavaScript including variables, functions, and control structures.', 1, 1),
(@js_course_id, 'DOM Manipulation & Events', 'dom-manipulation-events', 'Master the Document Object Model and interactive web development.', 2, 1),
(@js_course_id, 'Data Structures & Algorithms', 'data-structures-algorithms', 'Understand essential data structures and algorithmic thinking in JavaScript.', 3, 1),
(@js_course_id, 'Asynchronous Programming', 'asynchronous-programming', 'Learn promises, async/await, and handling asynchronous operations.', 4, 1),
(@js_course_id, 'Modern ES6+ Features', 'modern-es6-features', 'Explore the latest JavaScript features and modern development practices.', 5, 1),
(@js_course_id, 'Advanced Patterns & Best Practices', 'advanced-patterns-best-practices', 'Master advanced JavaScript patterns, design principles, and professional development.', 6, 1)
ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description);

-- Link existing JavaScript posts to course modules (assuming they exist)
-- This will need to be updated with actual post IDs after posts are created

-- Update posts to link to course modules (you'll need to adjust post titles/slugs as needed)
UPDATE posts p 
JOIN course_modules cm ON cm.course_id = @js_course_id
SET p.course_module_id = cm.id, p.lesson_order = 1 
WHERE p.title LIKE '%JavaScript Fundamentals%' AND cm.slug = 'javascript-fundamentals';

UPDATE posts p 
JOIN course_modules cm ON cm.course_id = @js_course_id
SET p.course_module_id = cm.id, p.lesson_order = 1 
WHERE p.title LIKE '%DOM Manipulation%' AND cm.slug = 'dom-manipulation-events';

UPDATE posts p 
JOIN course_modules cm ON cm.course_id = @js_course_id
SET p.course_module_id = cm.id, p.lesson_order = 1 
WHERE p.title LIKE '%Data Structures%' AND cm.slug = 'data-structures-algorithms';

UPDATE posts p 
JOIN course_modules cm ON cm.course_id = @js_course_id
SET p.course_module_id = cm.id, p.lesson_order = 1 
WHERE p.title LIKE '%Asynchronous%' AND cm.slug = 'asynchronous-programming';

UPDATE posts p 
JOIN course_modules cm ON cm.course_id = @js_course_id
SET p.course_module_id = cm.id, p.lesson_order = 1 
WHERE p.title LIKE '%ES6%' OR p.title LIKE '%Modern JavaScript%' AND cm.slug = 'modern-es6-features';

UPDATE posts p 
JOIN course_modules cm ON cm.course_id = @js_course_id
SET p.course_module_id = cm.id, p.lesson_order = 1 
WHERE p.title LIKE '%Advanced%' AND cm.slug = 'advanced-patterns-best-practices';

-- Create indexes for better performance
CREATE INDEX idx_posts_course_module ON posts(course_module_id);
CREATE INDEX idx_posts_lesson_order ON posts(lesson_order);
CREATE INDEX idx_course_progress_user_course ON course_progress(user_id, course_id);
CREATE INDEX idx_course_enrollments_user ON course_enrollments(user_id);

COMMIT;
