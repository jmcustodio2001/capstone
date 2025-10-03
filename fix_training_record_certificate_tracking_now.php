<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Checking if training_record_certificate_tracking table exists...\n";
    
    // Check if table exists
    if (Schema::hasTable('training_record_certificate_tracking')) {
        echo "Table training_record_certificate_tracking already exists!\n";
        exit(0);
    }
    
    echo "Creating training_record_certificate_tracking table...\n";
    
    // Create the table using raw SQL
    $sql = "
    CREATE TABLE IF NOT EXISTS `training_record_certificate_tracking` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `employee_id` varchar(50) NOT NULL,
        `course_id` bigint(20) unsigned NOT NULL,
        `training_date` date NOT NULL,
        `certificate_number` varchar(255) DEFAULT NULL,
        `certificate_expiry` date DEFAULT NULL,
        `certificate_url` varchar(255) DEFAULT NULL,
        `status` varchar(255) NOT NULL DEFAULT 'Active',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `training_record_certificate_tracking_employee_id_index` (`employee_id`),
        KEY `training_record_certificate_tracking_course_id_index` (`course_id`),
        KEY `training_record_certificate_tracking_status_index` (`status`),
        KEY `training_record_certificate_tracking_certificate_number_index` (`certificate_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    DB::unprepared($sql);
    echo "✓ Table training_record_certificate_tracking created successfully!\n";
    
    // Add foreign key constraints if referenced tables exist
    echo "Adding foreign key constraints...\n";
    
    // Check if employees table exists and add foreign key
    if (Schema::hasTable('employees')) {
        try {
            DB::unprepared("
                ALTER TABLE `training_record_certificate_tracking` 
                ADD CONSTRAINT `training_record_certificate_tracking_employee_id_foreign` 
                FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
            ");
            echo "✓ Foreign key constraint for employee_id added successfully!\n";
        } catch (Exception $e) {
            echo "⚠ Warning: Could not add employee_id foreign key: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠ Warning: employees table not found, skipping employee_id foreign key\n";
    }
    
    // Check if course_management table exists and add foreign key
    if (Schema::hasTable('course_management')) {
        try {
            DB::unprepared("
                ALTER TABLE `training_record_certificate_tracking` 
                ADD CONSTRAINT `training_record_certificate_tracking_course_id_foreign` 
                FOREIGN KEY (`course_id`) REFERENCES `course_management` (`course_id`) ON DELETE CASCADE
            ");
            echo "✓ Foreign key constraint for course_id added successfully!\n";
        } catch (Exception $e) {
            echo "⚠ Warning: Could not add course_id foreign key: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠ Warning: course_management table not found, skipping course_id foreign key\n";
    }
    
    // Verify table creation
    if (Schema::hasTable('training_record_certificate_tracking')) {
        echo "✓ Table verification successful!\n";
        
        // Test the query that was failing
        $count = DB::table('training_record_certificate_tracking')
            ->where('status', 'Completed')
            ->whereNotNull('certificate_number')
            ->count();
        echo "✓ Test query executed successfully! Found {$count} completed records with certificates.\n";
    } else {
        echo "✗ Error: Table verification failed!\n";
        exit(1);
    }
    
    echo "\n=== SUCCESS ===\n";
    echo "The training_record_certificate_tracking table has been created successfully!\n";
    echo "The SQLSTATE[42S02] error should now be resolved.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
