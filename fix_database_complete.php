<?php

// Complete database cleanup and repair script
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Starting comprehensive database cleanup and repair...\n";

// 1. Drop problematic view
try {
    DB::statement('DROP VIEW IF EXISTS destination_knowledge_training');
    echo "✓ Dropped problematic view: destination_knowledge_training\n";
} catch (Exception $e) {
    echo "- View destination_knowledge_training not found or already dropped\n";
}

// 2. List of unnecessary tables to drop
$tablesToDrop = [
    'employee_trainings',
    'trainings',
    'my_trainings', 
    'employee_my_trainings',
    'succession_assessments',
    'succession_candidates',
    'succession_development_activities', 
    'succession_history',
    'succession_readiness_ratings',
    'succession_scenarios',
    'succession_simulations',
    'training_notifications',
    'training_progress',
    'training_record_certificate_tracking',
    'training_reviews',
    'test_table',
    'temp_table'
];

$droppedTables = [];
$notFoundTables = [];

foreach ($tablesToDrop as $table) {
    try {
        if (Schema::hasTable($table)) {
            Schema::dropIfExists($table);
            $droppedTables[] = $table;
            echo "✓ Dropped table: {$table}\n";
        } else {
            $notFoundTables[] = $table;
            echo "- Table not found: {$table}\n";
        }
    } catch (Exception $e) {
        echo "✗ Error dropping table {$table}: " . $e->getMessage() . "\n";
    }
}

// 3. Fix foreign key constraints
echo "\nFixing foreign key constraints...\n";

// Check and fix competency_course_assignments
try {
    if (Schema::hasTable('competency_course_assignments')) {
        // Drop foreign key constraints that might reference dropped tables
        DB::statement('ALTER TABLE competency_course_assignments DROP FOREIGN KEY IF EXISTS competency_course_assignments_course_id_foreign');
        echo "✓ Fixed competency_course_assignments foreign keys\n";
    }
} catch (Exception $e) {
    echo "- No foreign key issues in competency_course_assignments\n";
}

// 4. Clean up orphaned records
echo "\nCleaning up orphaned records...\n";

try {
    // Clean up any records that reference non-existent tables
    $cleanupQueries = [
        "DELETE FROM employee_training_dashboards WHERE course_id NOT IN (SELECT course_id FROM course_management)",
        "DELETE FROM completed_trainings WHERE course_id NOT IN (SELECT course_id FROM course_management)",
        "DELETE FROM training_requests WHERE course_id NOT IN (SELECT course_id FROM course_management)"
    ];
    
    foreach ($cleanupQueries as $query) {
        try {
            DB::statement($query);
            echo "✓ Cleaned orphaned records\n";
        } catch (Exception $e) {
            echo "- No orphaned records to clean: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "- Cleanup completed with minor issues\n";
}

// 5. Recreate the view properly
echo "\nRecreating destination_knowledge_training view...\n";

try {
    if (Schema::hasTable('destination_knowledge_trainings')) {
        DB::statement("
            CREATE VIEW destination_knowledge_training AS 
            SELECT 
                id,
                employee_id,
                destination_name,
                details,
                date_completed,
                expired_date,
                delivery_mode,
                progress,
                remarks,
                status,
                is_active,
                admin_approved_for_upcoming,
                created_at,
                updated_at
            FROM destination_knowledge_trainings
            WHERE deleted_at IS NULL
        ");
        echo "✓ Recreated destination_knowledge_training view\n";
    } else {
        echo "- Base table destination_knowledge_trainings not found\n";
    }
} catch (Exception $e) {
    echo "✗ Error creating view: " . $e->getMessage() . "\n";
}

// 6. Check table connections and relationships
echo "\nChecking table relationships...\n";

$coreActiveTables = [
    'users',
    'employees', 
    'course_management',
    'employee_training_dashboards',
    'destination_knowledge_trainings',
    'customer_service_sales_skills_training',
    'competency_library',
    'competency_gaps',
    'employee_competency_profiles',
    'training_requests',
    'completed_trainings',
    'upcoming_trainings',
    'training_feedback',
    'leave_applications',
    'claim_reimbursements',
    'payslips',
    'attendance_time_logs',
    'activity_logs'
];

$connectedTables = [];
$unconnectedTables = [];

// Get all tables in database
$allTables = DB::select('SHOW TABLES');
$tableColumn = 'Tables_in_' . env('DB_DATABASE');

foreach ($allTables as $table) {
    $tableName = $table->$tableColumn;
    
    if (in_array($tableName, $coreActiveTables)) {
        $connectedTables[] = $tableName;
        echo "✓ Connected table: {$tableName}\n";
    } else {
        // Check if table has any relationships or is referenced
        $hasRelations = false;
        
        // Simple check for foreign key references
        try {
            $references = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE REFERENCED_TABLE_NAME = ? 
                AND TABLE_SCHEMA = ?
            ", [$tableName, env('DB_DATABASE')]);
            
            if ($references[0]->count > 0) {
                $hasRelations = true;
            }
        } catch (Exception $e) {
            // Continue checking
        }
        
        if (!$hasRelations && !in_array($tableName, ['migrations', 'cache', 'jobs', 'sessions', 'personal_access_tokens', 'failed_jobs'])) {
            $unconnectedTables[] = $tableName;
            echo "- Potentially unconnected: {$tableName}\n";
        }
    }
}

// Summary
echo "\n=== DATABASE CLEANUP SUMMARY ===\n";
echo "Tables dropped: " . count($droppedTables) . "\n";
echo "Connected tables: " . count($connectedTables) . "\n";
echo "Potentially unconnected tables: " . count($unconnectedTables) . "\n";

if (!empty($droppedTables)) {
    echo "\nDropped tables:\n";
    foreach ($droppedTables as $table) {
        echo "- {$table}\n";
    }
}

if (!empty($unconnectedTables)) {
    echo "\nPotentially unconnected tables (review needed):\n";
    foreach ($unconnectedTables as $table) {
        echo "- {$table}\n";
    }
}

echo "\nDatabase cleanup and repair completed!\n";
echo "Please review the results and test your application.\n";
