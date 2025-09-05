<?php

// Bootstrap Laravel
require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Creating employee_training_dashboard table...\n";
    
    // Check if table exists first
    if (!Schema::hasTable('employee_training_dashboard')) {
        // Create the table using Laravel's Schema builder
        Schema::create('employee_training_dashboard', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->unsignedBigInteger('course_id');
            $table->date('training_date')->nullable();
            $table->integer('progress')->default(0);
            $table->string('status')->default('Not Started');
            $table->text('remarks')->nullable();
            $table->timestamp('last_accessed')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('expired_date')->nullable();
            $table->timestamps();

            // Add indexes for better performance
            $table->index('employee_id');
            $table->index('course_id');
            $table->index('status');
        });
        
        echo "✅ employee_training_dashboard table created successfully!\n";
    } else {
        echo "ℹ️  employee_training_dashboard table already exists.\n";
    }
    
    // Verify the table was created
    $columns = Schema::getColumnListing('employee_training_dashboard');
    echo "Table columns: " . implode(', ', $columns) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    // Fallback: Try direct SQL execution
    try {
        echo "Trying direct SQL approach...\n";
        
        DB::statement("CREATE TABLE IF NOT EXISTS `employee_training_dashboard` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "✅ Table created using direct SQL!\n";
        
    } catch (Exception $sqlError) {
        echo "❌ SQL Error: " . $sqlError->getMessage() . "\n";
    }
}

echo "Script completed.\n";
