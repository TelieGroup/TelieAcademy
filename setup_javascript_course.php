<?php
require_once 'config/database.php';
require_once 'includes/Course.php';
require_once 'includes/Post.php';

echo "Setting up JavaScript Learning Journey Course...\n\n";

try {
    $conn = getDB();
    $course = new Course();
    $post = new Post();
    
    // First, run the database upgrade to add the new columns
    echo "1. Adding course columns to posts table...\n";
    
    // Check if course_module_id column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM posts LIKE 'course_module_id'")->fetchAll();
    if (empty($checkColumn)) {
        $conn->exec("ALTER TABLE posts 
                     ADD COLUMN course_module_id INT DEFAULT NULL AFTER category_id,
                     ADD COLUMN lesson_order INT DEFAULT 0 AFTER course_module_id,
                     ADD CONSTRAINT fk_posts_course_module 
                         FOREIGN KEY (course_module_id) REFERENCES course_modules(id) ON DELETE SET NULL");
        echo "✓ Course columns added to posts table\n";
    } else {
        echo "✓ Course columns already exist in posts table\n";
    }
    
    // Check if progress tables exist
    $checkProgress = $conn->query("SHOW TABLES LIKE 'course_progress'")->fetchAll();
    if (empty($checkProgress)) {
        echo "2. Creating course progress tables...\n";
        
        // Create course_progress table
        $conn->exec("CREATE TABLE course_progress (
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
        )");
        
        // Create course_enrollments table
        $conn->exec("CREATE TABLE course_enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            UNIQUE KEY unique_user_course (user_id, course_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )");
        
        echo "✓ Course progress tables created\n";
    } else {
        echo "✓ Course progress tables already exist\n";
    }
    
    // Check if JavaScript course exists
    echo "3. Setting up JavaScript Learning Journey course...\n";
    $jsCourse = $course->getCourseBySlug('javascript-learning-journey');
    
    if (!$jsCourse) {
        // Create the course
        $courseId = $course->createCourse(
            'JavaScript Learning Journey',
            'javascript-learning-journey',
            'Complete JavaScript course from beginner to advanced with hands-on practice and real-world projects.'
        );
        echo "✓ JavaScript Learning Journey course created (ID: $courseId)\n";
    } else {
        $courseId = $jsCourse['id'];
        echo "✓ JavaScript Learning Journey course already exists (ID: $courseId)\n";
    }
    
    // Create course modules
    echo "4. Setting up course modules...\n";
    $modules = [
        [
            'title' => 'JavaScript Fundamentals',
            'slug' => 'javascript-fundamentals',
            'description' => 'Learn the building blocks of JavaScript including variables, functions, and control structures.',
            'order' => 1
        ],
        [
            'title' => 'DOM Manipulation & Events',
            'slug' => 'dom-manipulation-events',
            'description' => 'Master the Document Object Model and interactive web development.',
            'order' => 2
        ],
        [
            'title' => 'Data Structures & Algorithms',
            'slug' => 'data-structures-algorithms',
            'description' => 'Understand essential data structures and algorithmic thinking in JavaScript.',
            'order' => 3
        ],
        [
            'title' => 'Asynchronous Programming',
            'slug' => 'asynchronous-programming',
            'description' => 'Learn promises, async/await, and handling asynchronous operations.',
            'order' => 4
        ],
        [
            'title' => 'Modern ES6+ Features',
            'slug' => 'modern-es6-features',
            'description' => 'Explore the latest JavaScript features and modern development practices.',
            'order' => 5
        ],
        [
            'title' => 'Advanced Patterns & Best Practices',
            'slug' => 'advanced-patterns-best-practices',
            'description' => 'Master advanced JavaScript patterns, design principles, and professional development.',
            'order' => 6
        ]
    ];
    
    $moduleIds = [];
    foreach ($modules as $moduleData) {
        $existingModule = $course->getModuleBySlug($courseId, $moduleData['slug']);
        if (!$existingModule) {
            $moduleId = $course->createModule(
                $courseId,
                $moduleData['title'],
                $moduleData['slug'],
                $moduleData['description'],
                $moduleData['order']
            );
            echo "✓ Created module: {$moduleData['title']} (ID: $moduleId)\n";
            $moduleIds[$moduleData['slug']] = $moduleId;
        } else {
            echo "✓ Module already exists: {$moduleData['title']} (ID: {$existingModule['id']})\n";
            $moduleIds[$moduleData['slug']] = $existingModule['id'];
        }
    }
    
    // Link existing JavaScript posts to modules
    echo "5. Linking existing JavaScript posts to course modules...\n";
    
    // Get all JavaScript posts
    $allPosts = $post->getAllPosts(null, 1000); // Get a large number of posts
    $linkedCount = 0;
    
    foreach ($allPosts as $postData) {
        $title = strtolower($postData['title']);
        $content = strtolower($postData['content']);
        $moduleId = null;
        $lessonOrder = 1;
        
        // Determine which module this post belongs to based on title and content
        if (strpos($title, 'fundamentals') !== false || 
            strpos($title, 'variables') !== false || 
            strpos($title, 'functions') !== false ||
            strpos($title, 'javascript fundamentals') !== false ||
            strpos($title, 'getting started') !== false) {
            $moduleId = $moduleIds['javascript-fundamentals'];
        }
        elseif (strpos($title, 'dom') !== false || 
                strpos($title, 'event') !== false || 
                strpos($title, 'manipulation') !== false ||
                strpos($content, 'document.') !== false) {
            $moduleId = $moduleIds['dom-manipulation-events'];
        }
        elseif (strpos($title, 'data structure') !== false || 
                strpos($title, 'algorithm') !== false || 
                strpos($title, 'array') !== false ||
                strpos($title, 'object') !== false) {
            $moduleId = $moduleIds['data-structures-algorithms'];
        }
        elseif (strpos($title, 'async') !== false || 
                strpos($title, 'promise') !== false || 
                strpos($title, 'await') !== false ||
                strpos($content, 'async') !== false ||
                strpos($content, 'promise') !== false) {
            $moduleId = $moduleIds['asynchronous-programming'];
        }
        elseif (strpos($title, 'es6') !== false || 
                strpos($title, 'modern') !== false || 
                strpos($title, 'template literal') !== false ||
                strpos($title, 'destructuring') !== false ||
                strpos($title, 'arrow function') !== false) {
            $moduleId = $moduleIds['modern-es6-features'];
        }
        elseif (strpos($title, 'advanced') !== false || 
                strpos($title, 'pattern') !== false || 
                strpos($title, 'closure') !== false ||
                strpos($title, 'prototype') !== false ||
                strpos($title, 'best practice') !== false) {
            $moduleId = $moduleIds['advanced-patterns-best-practices'];
        }
        
        // If we found a matching module, link the post
        if ($moduleId) {
            $updateQuery = "UPDATE posts SET course_module_id = :module_id, lesson_order = :lesson_order WHERE id = :post_id";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([
                'module_id' => $moduleId,
                'lesson_order' => $lessonOrder,
                'post_id' => $postData['id']
            ]);
            
            echo "✓ Linked post '{$postData['title']}' to module\n";
            $linkedCount++;
        }
    }
    
    echo "\n6. Creating indexes for better performance...\n";
    try {
        $conn->exec("CREATE INDEX idx_posts_course_module ON posts(course_module_id)");
        echo "✓ Created posts course module index\n";
    } catch (Exception $e) {
        echo "✓ Posts course module index already exists\n";
    }
    
    try {
        $conn->exec("CREATE INDEX idx_posts_lesson_order ON posts(lesson_order)");
        echo "✓ Created posts lesson order index\n";
    } catch (Exception $e) {
        echo "✓ Posts lesson order index already exists\n";
    }
    
    try {
        $conn->exec("CREATE INDEX idx_course_progress_user_course ON course_progress(user_id, course_id)");
        echo "✓ Created course progress index\n";
    } catch (Exception $e) {
        echo "✓ Course progress index already exists\n";
    }
    
    echo "\n🎉 Setup Complete!\n";
    echo "========================\n";
    echo "JavaScript Learning Journey course is ready!\n";
    echo "✓ Course created with 6 modules\n";
    echo "✓ $linkedCount posts linked to course modules\n";
    echo "✓ Progress tracking system active\n";
    echo "✓ Database indexes created\n\n";
    
    echo "🚀 Next Steps:\n";
    echo "1. Visit: /course-view?course=javascript-learning-journey\n";
    echo "2. Users can now enroll and track their progress\n";
    echo "3. Posts will automatically track reading progress\n";
    echo "4. Check the new 'Courses' dropdown in navigation\n\n";
    
    echo "📊 Course Statistics:\n";
    $modules = $course->getModulesWithPostsByCourse($courseId, true);
    foreach ($modules as $module) {
        $postCount = count($module['posts'] ?? []);
        echo "   • {$module['title']}: $postCount lessons\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
