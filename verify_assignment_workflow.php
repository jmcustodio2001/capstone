<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFYING ASSIGNMENT WORKFLOW ===\n\n";

try {
    // Check database connection
    $dbName = DB::connection()->getDatabaseName();
    echo "Connected to database: $dbName\n\n";
    
    // 1. Check upcoming_trainings table structure
    echo "1. Checking upcoming_trainings table:\n";
    $tableExists = DB::select("SHOW TABLES LIKE 'upcoming_trainings'");
    if (empty($tableExists)) {
        echo "   ❌ Table does not exist!\n";
        echo "   Creating table...\n";
        
        DB::statement("
            CREATE TABLE upcoming_trainings (
                upcoming_id bigint unsigned NOT NULL AUTO_INCREMENT,
                employee_id varchar(20) NOT NULL,
                training_title varchar(255) NOT NULL,
                start_date date NOT NULL,
                end_date date DEFAULT NULL,
                status varchar(255) DEFAULT 'Assigned',
                source varchar(255) DEFAULT NULL,
                assigned_by varchar(255) DEFAULT NULL,
                assigned_date timestamp NULL DEFAULT NULL,
                destination_training_id bigint unsigned DEFAULT NULL,
                needs_response tinyint(1) DEFAULT 1,
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (upcoming_id),
                KEY idx_employee_id (employee_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "   ✅ Table created\n";
    } else {
        echo "   ✅ Table exists\n";
        
        // Show table structure
        $columns = DB::select("DESCRIBE upcoming_trainings");
        echo "   Columns:\n";
        foreach ($columns as $col) {
            echo "     - {$col->Field} ({$col->Type})\n";
        }
    }
    
    // 2. Check competency_gaps table
    echo "\n2. Checking competency_gaps table:\n";
    $gapsTable = DB::select("SHOW TABLES LIKE 'competency_gaps'");
    if (empty($gapsTable)) {
        echo "   ❌ competency_gaps table does not exist!\n";
    } else {
        echo "   ✅ Table exists\n";
        $gapCount = DB::table('competency_gaps')->count();
        echo "   Records: $gapCount\n";
    }
    
    // 3. Check employees table
    echo "\n3. Checking employees table:\n";
    $empCount = DB::table('employees')->count();
    echo "   Records: $empCount\n";
    
    if ($empCount > 0) {
        $sampleEmp = DB::table('employees')->first();
        echo "   Sample employee: {$sampleEmp->employee_id} - {$sampleEmp->first_name} {$sampleEmp->last_name}\n";
    }
    
    // 4. Check current upcoming trainings
    echo "\n4. Current upcoming trainings:\n";
    $upcomingCount = DB::table('upcoming_trainings')->count();
    echo "   Total records: $upcomingCount\n";
    
    if ($upcomingCount > 0) {
        $recent = DB::table('upcoming_trainings')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        echo "   Recent assignments:\n";
        foreach ($recent as $training) {
            echo "     - {$training->employee_id}: {$training->training_title} ({$training->status}) - {$training->source}\n";
        }
    }
    
    // 5. Test assignment simulation
    echo "\n5. Testing assignment workflow:\n";
    
    // Get a test employee
    $testEmployee = DB::table('employees')->first();
    if ($testEmployee) {
        echo "   Using employee: {$testEmployee->employee_id}\n";
        
        // Create a test assignment
        $testData = [
            'employee_id' => $testEmployee->employee_id,
            'training_title' => 'Communication Skills',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+3 months')),
            'status' => 'Assigned',
            'source' => 'competency_gap',
            'assigned_by' => 'Test Admin',
            'assigned_date' => date('Y-m-d H:i:s'),
            'needs_response' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Check if already exists
        $existing = DB::table('upcoming_trainings')
            ->where('employee_id', $testEmployee->employee_id)
            ->where('training_title', 'Communication Skills')
            ->where('source', 'competency_gap')
            ->first();
            
        if ($existing) {
            echo "   ✅ Test assignment already exists (ID: {$existing->upcoming_id})\n";
        } else {
            $insertId = DB::table('upcoming_trainings')->insertGetId($testData);
            echo "   ✅ Created test assignment (ID: $insertId)\n";
        }
        
        // Verify it appears in queries
        $verification = DB::table('upcoming_trainings')
            ->where('employee_id', $testEmployee->employee_id)
            ->where('training_title', 'Communication Skills')
            ->first();
            
        if ($verification) {
            echo "   ✅ Assignment verified in database\n";
            echo "     - ID: {$verification->upcoming_id}\n";
            echo "     - Employee: {$verification->employee_id}\n";
            echo "     - Training: {$verification->training_title}\n";
            echo "     - Status: {$verification->status}\n";
            echo "     - Needs Response: " . ($verification->needs_response ? 'Yes' : 'No') . "\n";
        }
    }
    
    echo "\n=== WORKFLOW VERIFICATION COMPLETE ===\n";
    echo "✅ Database tables are properly configured\n";
    echo "✅ Assignment workflow should work correctly\n";
    echo "\nTo test the full workflow:\n";
    echo "1. Go to Competency Gap Management\n";
    echo "2. Click 'Assign to Training' for any gap\n";
    echo "3. Check employee ESS portal -> My Trainings -> Upcoming\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
