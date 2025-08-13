-- Course Notes System Database Setup
-- This system allows admins to organize course materials and premium users to access them

USE telie_academy;

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create modules table (subdivisions within courses)
CREATE TABLE IF NOT EXISTS course_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_module_slug (course_id, slug)
);

-- Create course materials table (PDFs, PowerPoints, etc.)
CREATE TABLE IF NOT EXISTS course_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    download_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
);

-- Create user material access tracking
CREATE TABLE IF NOT EXISTS user_material_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    material_id INT NOT NULL,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    download_count INT DEFAULT 0,
    last_downloaded TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES course_materials(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_material (user_id, material_id)
);

-- Add indexes for better performance
CREATE INDEX idx_courses_slug ON courses(slug);
CREATE INDEX idx_courses_active ON courses(is_active);
CREATE INDEX idx_modules_course_id ON course_modules(course_id);
CREATE INDEX idx_modules_slug ON course_modules(slug);
CREATE INDEX idx_modules_active ON course_modules(is_active);
CREATE INDEX idx_materials_module_id ON course_materials(module_id);
CREATE INDEX idx_materials_active ON course_materials(is_active);
CREATE INDEX idx_materials_file_type ON course_materials(file_type);
CREATE INDEX idx_user_access_user_id ON user_material_access(user_id);
CREATE INDEX idx_user_access_material_id ON user_material_access(material_id);

-- Insert sample data for testing
INSERT INTO courses (title, slug, description) VALUES 
('Web Development Fundamentals', 'web-dev-fundamentals', 'Learn the basics of web development including HTML, CSS, and JavaScript'),
('Advanced JavaScript', 'advanced-javascript', 'Master advanced JavaScript concepts and modern ES6+ features'),
('React Development', 'react-development', 'Build modern web applications with React framework');

INSERT INTO course_modules (course_id, title, slug, description, order_index) VALUES 
(1, 'HTML Basics', 'html-basics', 'Introduction to HTML markup language', 1),
(1, 'CSS Styling', 'css-styling', 'Learn CSS for styling web pages', 2),
(1, 'JavaScript Fundamentals', 'javascript-fundamentals', 'Basic JavaScript programming concepts', 3),
(2, 'ES6 Features', 'es6-features', 'Modern JavaScript ES6+ syntax and features', 1),
(2, 'Async Programming', 'async-programming', 'Promises, async/await, and asynchronous JavaScript', 2),
(3, 'React Components', 'react-components', 'Building reusable React components', 1),
(3, 'State Management', 'state-management', 'Managing state in React applications', 2);
