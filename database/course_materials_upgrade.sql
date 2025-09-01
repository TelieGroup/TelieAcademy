-- Course Materials Enhancement for Progressive Learning
-- This script adds new columns and features to integrate materials with the progressive learning system

-- Add new columns to course_materials table for progressive learning integration
ALTER TABLE course_materials 
ADD COLUMN IF NOT EXISTS required_lesson_id INT NULL COMMENT 'Post ID that must be completed to access this material',
ADD COLUMN IF NOT EXISTS related_lesson_id INT NULL COMMENT 'Post ID this material is related to',
ADD COLUMN IF NOT EXISTS order_index INT DEFAULT 0 COMMENT 'Order of material within module',
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether material is active',
ADD COLUMN IF NOT EXISTS download_count INT DEFAULT 0 COMMENT 'Total download count',
ADD INDEX idx_required_lesson (required_lesson_id),
ADD INDEX idx_related_lesson (related_lesson_id),
ADD INDEX idx_order (order_index),
ADD INDEX idx_active (is_active);

-- Add foreign key constraints if they don't exist
ALTER TABLE course_materials 
ADD CONSTRAINT fk_materials_required_lesson 
FOREIGN KEY (required_lesson_id) REFERENCES posts(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_materials_related_lesson 
FOREIGN KEY (related_lesson_id) REFERENCES posts(id) ON DELETE SET NULL;

-- Enhance user_material_access table
ALTER TABLE user_material_access 
ADD COLUMN IF NOT EXISTS download_count INT DEFAULT 1 COMMENT 'User-specific download count',
ADD COLUMN IF NOT EXISTS first_downloaded TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'First download timestamp',
ADD COLUMN IF NOT EXISTS last_downloaded TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last download timestamp',
ADD INDEX idx_user_downloads (user_id, download_count),
ADD INDEX idx_last_download (last_downloaded);

-- Sample data: Add some materials for the JavaScript course
SET @js_course_id = (SELECT id FROM courses WHERE slug = 'javascript-learning-journey');

-- Get module IDs
SET @fundamentals_module = (SELECT id FROM course_modules WHERE course_id = @js_course_id AND slug = 'js-fundamentals');
SET @dom_module = (SELECT id FROM course_modules WHERE course_id = @js_course_id AND slug = 'dom-manipulation');
SET @datastructures_module = (SELECT id FROM course_modules WHERE course_id = @js_course_id AND slug = 'js-data-structures');
SET @async_module = (SELECT id FROM course_modules WHERE course_id = @js_course_id AND slug = 'async-javascript');
SET @modern_module = (SELECT id FROM course_modules WHERE course_id = @js_course_id AND slug = 'modern-javascript-es6');
SET @advanced_module = (SELECT id FROM course_modules WHERE course_id = @js_course_id AND slug = 'advanced-js-patterns');

-- Create sample materials for each module
INSERT IGNORE INTO course_materials (module_id, title, description, file_name, file_path, file_size, file_type, order_index, is_active) VALUES
-- JavaScript Fundamentals Materials
(@fundamentals_module, 'JavaScript Basics Cheat Sheet', 'Quick reference guide for JavaScript fundamentals including variables, data types, and operators.', 'js-basics-cheatsheet.pdf', '/uploads/course_materials/js-basics-cheatsheet.pdf', 1024000, 'pdf', 1, TRUE),
(@fundamentals_module, 'Variable Declaration Best Practices', 'Comprehensive guide on when to use var, let, and const in JavaScript.', 'variable-best-practices.pdf', '/uploads/course_materials/variable-best-practices.pdf', 512000, 'pdf', 2, TRUE),

-- DOM Manipulation Materials  
(@dom_module, 'DOM Methods Reference Guide', 'Complete reference for DOM manipulation methods and properties.', 'dom-methods-reference.pdf', '/uploads/course_materials/dom-methods-reference.pdf', 2048000, 'pdf', 1, TRUE),
(@dom_module, 'Event Handling Patterns', 'Modern patterns and best practices for handling events in JavaScript.', 'event-handling-patterns.pdf', '/uploads/course_materials/event-handling-patterns.pdf', 1536000, 'pdf', 2, TRUE),
(@dom_module, 'DOM Manipulation Examples', 'Practical examples and code snippets for common DOM operations.', 'dom-examples.pdf', '/uploads/course_materials/dom-examples.pdf', 1280000, 'pdf', 3, TRUE),

-- Data Structures Materials
(@datastructures_module, 'JavaScript Data Structures Guide', 'In-depth guide to arrays, objects, maps, sets, and other data structures.', 'js-data-structures.pdf', '/uploads/course_materials/js-data-structures.pdf', 3072000, 'pdf', 1, TRUE),

-- Asynchronous JavaScript Materials
(@async_module, 'Promises and Async/Await Guide', 'Master asynchronous JavaScript with promises, async/await, and error handling.', 'async-js-guide.pdf', '/uploads/course_materials/async-js-guide.pdf', 2560000, 'pdf', 1, TRUE),
(@async_module, 'API Integration Examples', 'Real-world examples of working with REST APIs using fetch and axios.', 'api-integration-examples.pdf', '/uploads/course_materials/api-integration-examples.pdf', 1792000, 'pdf', 2, TRUE),

-- Modern JavaScript Materials
(@modern_module, 'ES6+ Features Reference', 'Complete guide to modern JavaScript features from ES6 to ES2023.', 'es6-features-reference.pdf', '/uploads/course_materials/es6-features-reference.pdf', 2816000, 'pdf', 1, TRUE),
(@modern_module, 'Module System Guide', 'Understanding ES6 modules, import/export, and module bundling.', 'module-system-guide.pdf', '/uploads/course_materials/module-system-guide.pdf', 1408000, 'pdf', 2, TRUE),

-- Advanced Patterns Materials
(@advanced_module, 'Design Patterns in JavaScript', 'Implementation of common design patterns in JavaScript.', 'js-design-patterns.pdf', '/uploads/course_materials/js-design-patterns.pdf', 4096000, 'pdf', 1, TRUE),
(@advanced_module, 'Performance Optimization Techniques', 'Advanced techniques for optimizing JavaScript performance.', 'js-performance-optimization.pdf', '/uploads/course_materials/js-performance-optimization.pdf', 2304000, 'pdf', 2, TRUE);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_materials_module_order ON course_materials(module_id, order_index);
CREATE INDEX IF NOT EXISTS idx_materials_downloads ON course_materials(download_count DESC);
CREATE INDEX IF NOT EXISTS idx_access_user_material ON user_material_access(user_id, material_id);

-- Update existing materials to have proper order
UPDATE course_materials SET order_index = id WHERE order_index = 0;

SELECT 'Course materials upgrade completed successfully!' as status;

