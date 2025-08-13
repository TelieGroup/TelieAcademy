<?php
require_once 'config/database.php';

echo "Setting up Course Notes System...\n\n";

try {
    $conn = getDB();
    
    // Create courses table
    echo "Creating courses table...\n";
    $createCoursesTable = "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        description TEXT,
        thumbnail VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->exec($createCoursesTable) !== false) {
        echo "✓ courses table created/verified\n";
    } else {
        echo "✗ Failed to create courses table\n";
    }
    
    // Create course_modules table
    echo "Creating course_modules table...\n";
    $createModulesTable = "CREATE TABLE IF NOT EXISTS course_modules (
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
    )";
    
    if ($conn->exec($createModulesTable) !== false) {
        echo "✓ course_modules table created/verified\n";
    } else {
        echo "✗ Failed to create course_modules table\n";
    }
    
    // Create course_materials table
    echo "Creating course_materials table...\n";
    $createMaterialsTable = "CREATE TABLE IF NOT EXISTS course_materials (
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
    )";
    
    if ($conn->exec($createMaterialsTable) !== false) {
        echo "✓ course_materials table created/verified\n";
    } else {
        echo "✗ Failed to create course_materials table\n";
    }
    
    // Create user_material_access table
    echo "Creating user_material_access table...\n";
    $createAccessTable = "CREATE TABLE IF NOT EXISTS user_material_access (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        material_id INT NOT NULL,
        accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        download_count INT DEFAULT 0,
        last_downloaded TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (material_id) REFERENCES course_materials(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_material (user_id, material_id)
    )";
    
    if ($conn->exec($createAccessTable) !== false) {
        echo "✓ user_material_access table created/verified\n";
    } else {
        echo "✗ Failed to create user_material_access table\n";
    }
    
    // Add indexes
    echo "\nAdding indexes...\n";
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_courses_slug ON courses(slug)",
        "CREATE INDEX IF NOT EXISTS idx_courses_active ON courses(is_active)",
        "CREATE INDEX IF NOT EXISTS idx_modules_course_id ON course_modules(course_id)",
        "CREATE INDEX IF NOT EXISTS idx_modules_slug ON course_modules(slug)",
        "CREATE INDEX IF NOT EXISTS idx_modules_active ON course_modules(is_active)",
        "CREATE INDEX IF NOT EXISTS idx_materials_module_id ON course_materials(module_id)",
        "CREATE INDEX IF NOT EXISTS idx_materials_active ON course_materials(is_active)",
        "CREATE INDEX IF NOT EXISTS idx_materials_file_type ON course_materials(file_type)",
        "CREATE INDEX IF NOT EXISTS idx_user_access_user_id ON user_material_access(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_user_access_material_id ON user_material_access(material_id)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $conn->exec($index);
            echo "✓ Index created\n";
        } catch (Exception $e) {
            echo "! Index may already exist: " . $e->getMessage() . "\n";
        }
    }
    
    // Insert sample data
    echo "\nInserting sample data...\n";
    
    // Check if sample courses already exist
    $existingCourses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch();
    if ($existingCourses['count'] == 0) {
        $sampleCourses = [
            ['Web Development Fundamentals', 'web-dev-fundamentals', 'Learn the basics of web development including HTML, CSS, and JavaScript'],
            ['Advanced JavaScript', 'advanced-javascript', 'Master advanced JavaScript concepts and modern ES6+ features'],
            ['React Development', 'react-development', 'Build modern web applications with React framework']
        ];
        
        foreach ($sampleCourses as $course) {
            $stmt = $conn->prepare("INSERT INTO courses (title, slug, description) VALUES (?, ?, ?)");
            if ($stmt->execute($course)) {
                echo "✓ Sample course added: {$course[0]}\n";
            }
        }
        
        // Add sample modules
        $courseIds = $conn->query("SELECT id FROM courses")->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($courseIds)) {
            $sampleModules = [
                [$courseIds[0], 'HTML Basics', 'html-basics', 'Introduction to HTML markup language', 1],
                [$courseIds[0], 'CSS Styling', 'css-styling', 'Learn CSS for styling web pages', 2],
                [$courseIds[0], 'JavaScript Fundamentals', 'javascript-fundamentals', 'Basic JavaScript programming concepts', 3],
                [$courseIds[1], 'ES6 Features', 'es6-features', 'Modern JavaScript ES6+ syntax and features', 1],
                [$courseIds[1], 'Async Programming', 'async-programming', 'Promises, async/await, and asynchronous JavaScript', 2],
                [$courseIds[2], 'React Components', 'react-components', 'Building reusable React components', 1],
                [$courseIds[2], 'State Management', 'state-management', 'Managing state in React applications', 2]
            ];
            
            foreach ($sampleModules as $module) {
                $stmt = $conn->prepare("INSERT INTO course_modules (course_id, title, slug, description, order_index) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute($module)) {
                    echo "✓ Sample module added: {$module[1]}\n";
                }
            }
        }
    } else {
        echo "Sample data already exists\n";
    }
    
    echo "\n✓ Course Notes System setup complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
