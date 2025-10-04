<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking and fixing migration issues...\n";

// List of migrations that should be marked as completed if their tables exist
$migrations_to_check = [
    '2025_01_15_100000_create_destination_knowledge_trainings_table' => 'destination_knowledge_trainings',
    '2025_08_19_000001_create_destination_knowledge_trainings_table' => 'destination_knowledge_trainings',
    '2025_01_16_110000_create_upcoming_trainings_table' => 'upcoming_trainings',
    '2025_08_21_180000_create_upcoming_trainings_table' => 'upcoming_trainings',
    '2025_08_03_114549_create_personal_access_tokens_table' => 'personal_access_tokens',
    '2025_01_22_000000_add_otp_fields_to_employees_table' => 'employees',
    '0001_01_01_000000_create_users_table' => 'users',
    '0001_01_01_000001_create_cache_table' => 'cache',
    '0001_01_01_000002_create_jobs_table' => 'jobs',
];

$batch = DB::table('migrations')->max('batch') + 1;

foreach ($migrations_to_check as $migration => $table) {
    // Check if migration record exists
    $migration_exists = DB::table('migrations')->where('migration', $migration)->exists();
    
    // Check if table exists
    $table_exists = Schema::hasTable($table);
    
    // For OTP fields migration, check if OTP columns exist
    if ($migration === '2025_01_22_000000_add_otp_fields_to_employees_table') {
        $table_exists = Schema::hasColumn('employees', 'otp_code');
    }
    
    echo "Migration: $migration\n";
    echo "  Table '$table' exists: " . ($table_exists ? 'YES' : 'NO') . "\n";
    echo "  Migration record exists: " . ($migration_exists ? 'YES' : 'NO') . "\n";
    
    if ($table_exists && !$migration_exists) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
        echo "  ✓ Added migration record\n";
    } elseif (!$table_exists && $migration_exists) {
        echo "  ⚠ Warning: Migration record exists but table is missing\n";
    } elseif ($table_exists && $migration_exists) {
        echo "  ✓ Already properly recorded\n";
    } else {
        echo "  - No action needed\n";
    }
    echo "\n";
}

echo "Migration fix completed!\n";

// Fix foreign key constraints for destination_knowledge_trainings table
echo "\nFixing foreign key constraints...\n";

try {
    // Check if destination_knowledge_trainings table exists and employees table exists
    if (Schema::hasTable('destination_knowledge_trainings') && Schema::hasTable('employees')) {
        echo "Both tables exist, checking foreign key constraint...\n";
        
        // Check if foreign key already exists
        $foreignKeyExists = false;
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'destination_knowledge_trainings' 
                AND CONSTRAINT_NAME LIKE '%employee_id_foreign%'
            ");
            $foreignKeyExists = !empty($foreignKeys);
        } catch (Exception $e) {
            echo "Could not check existing foreign keys: " . $e->getMessage() . "\n";
        }
        
        if (!$foreignKeyExists) {
            echo "Adding foreign key constraint for destination_knowledge_trainings...\n";
            
            // Add foreign key constraint
            DB::statement("
                ALTER TABLE destination_knowledge_trainings 
                ADD CONSTRAINT destination_knowledge_trainings_employee_id_foreign 
                FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
            ");
            
            echo "✓ Foreign key constraint added successfully!\n";
        } else {
            echo "✓ Foreign key constraint already exists\n";
        }
    } else {
        echo "Required tables don't exist yet\n";
    }
    
    // Fix other tables that might have similar issues
    $tablesToFix = [
        'upcoming_trainings',
        'completed_trainings', 
        'attendance_time_logs',
        'payslips',
        'customer_service_sales_skills_training',
        'potential_successors',
        'succession_readiness_ratings',
        'succession_simulations'
    ];
    
    foreach ($tablesToFix as $tableName) {
        if (Schema::hasTable($tableName) && Schema::hasTable('employees') && Schema::hasColumn($tableName, 'employee_id')) {
            echo "Checking foreign key for table: $tableName\n";
            
            // Check if foreign key already exists
            $foreignKeyExists = false;
            try {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '$tableName' 
                    AND CONSTRAINT_NAME LIKE '%employee_id_foreign%'
                ");
                $foreignKeyExists = !empty($foreignKeys);
            } catch (Exception $e) {
                // Continue if we can't check
            }
            
            if (!$foreignKeyExists) {
                try {
                    $constraintName = $tableName . '_employee_id_foreign';
                    DB::statement("
                        ALTER TABLE $tableName 
                        ADD CONSTRAINT $constraintName 
                        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
                    ");
                    echo "✓ Added foreign key for $tableName\n";
                } catch (Exception $e) {
                    echo "⚠ Could not add foreign key for $tableName: " . $e->getMessage() . "\n";
                }
            } else {
                echo "✓ Foreign key already exists for $tableName\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error fixing foreign key constraints: " . $e->getMessage() . "\n";
}

echo "\nForeign key constraint fix completed!\n";
