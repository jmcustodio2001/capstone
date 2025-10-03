<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Load Laravel configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Check if table exists
    if (!Schema::hasTable('succession_readiness_ratings')) {
        echo "Creating succession_readiness_ratings table...\n";
        
        // Create the table using raw SQL
        DB::statement("
            CREATE TABLE `succession_readiness_ratings` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` varchar(20) NOT NULL,
                `readiness_score` int(11) NOT NULL,
                `assessment_date` date NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `succession_readiness_ratings_employee_id_foreign` (`employee_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "Table 'succession_readiness_ratings' created successfully!\n";
        
        // Add foreign key constraint if employees table exists
        if (Schema::hasTable('employees')) {
            try {
                DB::statement("
                    ALTER TABLE `succession_readiness_ratings` 
                    ADD CONSTRAINT `succession_readiness_ratings_employee_id_foreign` 
                    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
                ");
                echo "Foreign key constraint added successfully!\n";
            } catch (Exception $e) {
                echo "Note: Could not add foreign key constraint (employees table may not exist): " . $e->getMessage() . "\n";
            }
        }
        
        // Insert sample data
        DB::table('succession_readiness_ratings')->insert([
            [
                'employee_id' => 'EMP001',
                'readiness_score' => 85,
                'assessment_date' => '2024-01-15',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 'EMP002', 
                'readiness_score' => 92,
                'assessment_date' => '2024-01-15',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 'EMP003',
                'readiness_score' => 78,
                'assessment_date' => '2024-01-15', 
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        echo "Sample data inserted successfully!\n";
        
    } else {
        echo "Table 'succession_readiness_ratings' already exists.\n";
    }
    
    // Verify the table was created
    $count = DB::table('succession_readiness_ratings')->count();
    echo "Table verification: Found {$count} records in succession_readiness_ratings table.\n";
    
    echo "Fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
