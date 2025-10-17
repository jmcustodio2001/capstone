<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the sync functionality
echo "Testing Competency to Course Management Sync...\n";

try {
    // Get competency count
    $competencyCount = \App\Models\CompetencyLibrary::count();
    echo "Found {$competencyCount} competencies in competency library\n";
    
    // Get current course count
    $courseCountBefore = \App\Models\CourseManagement::count();
    echo "Current courses in course management: {$courseCountBefore}\n";
    
    // Create an instance of the controller and call the sync method
    $controller = new \App\Http\Controllers\CourseManagementController();
    
    // Use reflection to call the private method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('syncCompetenciesToCourses');
    $method->setAccessible(true);
    $method->invoke($controller);
    
    // Get course count after sync
    $courseCountAfter = \App\Models\CourseManagement::count();
    echo "Courses after sync: {$courseCountAfter}\n";
    
    $synced = $courseCountAfter - $courseCountBefore;
    echo "Successfully synced {$synced} new courses from competencies!\n";
    
    // Show some examples
    echo "\nFirst 5 courses:\n";
    $courses = \App\Models\CourseManagement::take(5)->get();
    foreach ($courses as $course) {
        echo "- {$course->course_title} (Status: {$course->status})\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
