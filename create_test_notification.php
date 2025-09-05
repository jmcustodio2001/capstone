<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create test destination knowledge training record
$employee = \App\Models\Employee::first();

if ($employee) {
    $training = \App\Models\DestinationKnowledgeTraining::create([
        'employee_id' => $employee->employee_id,
        'destination_name' => 'Boracay Training - Test Notification',
        'details' => 'Test destination knowledge training for notification system verification',
        'progress' => 50,
        'status' => 'in-progress',
        'is_active' => true,
        'admin_approved_for_upcoming' => false
    ]);
    
    echo "Created test record with ID: " . $training->id . "\n";
    echo "Employee ID: " . $training->employee_id . "\n";
    echo "Destination: " . $training->destination_name . "\n";
    echo "Status: " . $training->status . "\n";

    // Create missing employee_training_dashboard table
    echo "Creating missing employee_training_dashboard table...\n";

    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'hr2system';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "Connected to database successfully.\n";
        
        // Create the missing table
        $sql = "CREATE TABLE IF NOT EXISTS `employee_training_dashboard` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `employee_id` varchar(20) NOT NULL,
            `course_id` bigint(20) UNSIGNED NOT NULL,
            `training_date` date DEFAULT NULL,
            `progress` int(11) NOT NULL DEFAULT 0,
            `status` varchar(255) NOT NULL DEFAULT 'Not Started',
            `remarks` text DEFAULT NULL,
            `last_accessed` timestamp NULL DEFAULT NULL,
            `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
            `expired_date` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `employee_training_dashboard_employee_id_index` (`employee_id`),
            KEY `employee_training_dashboard_course_id_index` (`course_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✓ Table 'employee_training_dashboard' created successfully!\n";
        
        // Verify table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'employee_training_dashboard'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table verification: SUCCESS\n";
            echo "✓ The my_trainings page should now work without errors.\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }

    echo "Admin Approved: " . ($training->admin_approved_for_upcoming ? 'Yes' : 'No') . "\n";
    echo "Active: " . ($training->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "No employees found in database\n";
}

// Check existing records
$allRecords = \App\Models\DestinationKnowledgeTraining::all();
echo "\nTotal destination knowledge training records: " . $allRecords->count() . "\n";

$pendingRecords = \App\Models\DestinationKnowledgeTraining::where('admin_approved_for_upcoming', false)
    ->where('is_active', true)
    ->get();
echo "Records needing approval: " . $pendingRecords->count() . "\n";

foreach ($pendingRecords as $record) {
    echo "- ID: {$record->id}, Destination: {$record->destination_name}, Employee: {$record->employee_id}\n";
}
