<?php

// Direct database connection to fix the Take Exam button issue
$host = 'localhost';
$dbname = 'hr2ess';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    echo "Fixing Communication Skills course_id issue...\n\n";
    
    // Check if Communication Skills course exists
    $stmt = $pdo->prepare("SELECT course_id FROM courses WHERE course_title = 'Communication Skills'");
    $stmt->execute();
    $course = $stmt->fetch();
    
    if (!$course) {
        // Create Communication Skills course
        echo "Creating Communication Skills course...\n";
        $stmt = $pdo->prepare("INSERT INTO courses (course_title, description, duration_hours, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute(['Communication Skills', 'Develop effective communication skills for professional success', 8]);
        $courseId = $pdo->lastInsertId();
        echo "Created course with ID: $courseId\n";
    } else {
        $courseId = $course['course_id'];
        echo "Found existing Communication Skills course with ID: $courseId\n";
    }
    
    // Update training_requests to set course_id for Communication Skills
    $stmt = $pdo->prepare("UPDATE training_requests SET course_id = ? WHERE training_title = 'Communication Skills' AND (course_id IS NULL OR course_id = 0)");
    $stmt->execute([$courseId]);
    $updated = $stmt->rowCount();
    echo "Updated $updated training request(s) with course_id: $courseId\n";
    
    // Verify the update
    $stmt = $pdo->prepare("SELECT request_id, training_title, course_id, status FROM training_requests WHERE training_title = 'Communication Skills'");
    $stmt->execute();
    $requests = $stmt->fetchAll();
    
    echo "\nVerification - Training requests for Communication Skills:\n";
    foreach ($requests as $request) {
        echo "Request ID: {$request['request_id']}, Course ID: {$request['course_id']}, Status: {$request['status']}\n";
    }
    
    // Also create some exam questions for Communication Skills if they don't exist
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM exam_questions WHERE course_id = ?");
    $stmt->execute([$courseId]);
    $questionCount = $stmt->fetch()['count'];
    
    if ($questionCount == 0) {
        echo "\nCreating sample exam questions for Communication Skills...\n";
        
        $questions = [
            [
                'question' => 'What is the most important aspect of effective communication?',
                'option_a' => 'Speaking loudly',
                'option_b' => 'Active listening',
                'option_c' => 'Using complex vocabulary',
                'option_d' => 'Talking fast',
                'correct_answer' => 'B'
            ],
            [
                'question' => 'Which of the following is a barrier to effective communication?',
                'option_a' => 'Clear language',
                'option_b' => 'Active listening',
                'option_c' => 'Noise and distractions',
                'option_d' => 'Eye contact',
                'correct_answer' => 'C'
            ],
            [
                'question' => 'What does non-verbal communication include?',
                'option_a' => 'Body language and facial expressions',
                'option_b' => 'Only spoken words',
                'option_c' => 'Written text',
                'option_d' => 'Email messages',
                'correct_answer' => 'A'
            ],
            [
                'question' => 'What is the purpose of feedback in communication?',
                'option_a' => 'To criticize others',
                'option_b' => 'To confirm understanding',
                'option_c' => 'To show superiority',
                'option_d' => 'To end conversations',
                'correct_answer' => 'B'
            ],
            [
                'question' => 'Which communication style is most effective in professional settings?',
                'option_a' => 'Aggressive',
                'option_b' => 'Passive',
                'option_c' => 'Assertive',
                'option_d' => 'Passive-aggressive',
                'correct_answer' => 'C'
            ]
        ];
        
        foreach ($questions as $q) {
            $stmt = $pdo->prepare("INSERT INTO exam_questions (course_id, question, option_a, option_b, option_c, option_d, correct_answer, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$courseId, $q['question'], $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'], $q['correct_answer']]);
        }
        
        echo "Created " . count($questions) . " sample exam questions.\n";
    } else {
        echo "\nFound $questionCount existing exam questions for Communication Skills.\n";
    }
    
    echo "\n✅ Fix completed! The Take Exam button should now appear for approved Communication Skills training requests.\n";
    echo "Please refresh the page to see the changes.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
