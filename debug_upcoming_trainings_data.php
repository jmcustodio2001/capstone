<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\UpcomingTraining;

echo "=== DEBUGGING UPCOMING TRAININGS DATA ===\n\n";

try {
    // Check if upcoming_trainings table exists
    echo "1. Checking upcoming_trainings table...\n";
    $tableExists = DB::select("SHOW TABLES LIKE 'upcoming_trainings'");
    if (empty($tableExists)) {
        echo "   ❌ Table does not exist! Creating it...\n";
        
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
    }
    
    // Check all records in upcoming_trainings
    echo "\n2. All records in upcoming_trainings table:\n";
    $allRecords = DB::table('upcoming_trainings')->get();
    echo "   Total records: " . $allRecords->count() . "\n";
    
    if ($allRecords->count() > 0) {
        foreach ($allRecords as $record) {
            echo "   - ID: {$record->upcoming_id}, Employee: {$record->employee_id}, Training: {$record->training_title}, Status: {$record->status}, Source: {$record->source}\n";
        }
    } else {
        echo "   ❌ No records found in upcoming_trainings table\n";
    }
    
    // Check for JM CUSTODIO specifically
    echo "\n3. Checking for JM CUSTODIO (EMP001):\n";
    $jmRecords = DB::table('upcoming_trainings')->where('employee_id', 'EMP001')->get();
    echo "   Records for EMP001: " . $jmRecords->count() . "\n";
    
    // Check all employees
    echo "\n4. All employees in system:\n";
    $employees = DB::table('employees')->select('employee_id', 'first_name', 'last_name')->get();
    foreach ($employees->take(5) as $emp) {
        echo "   - {$emp->employee_id}: {$emp->first_name} {$emp->last_name}\n";
    }
    
    // Create test assignment for JM CUSTODIO
    echo "\n5. Creating test assignment for JM CUSTODIO...\n";
    $testEmployee = DB::table('employees')->where('employee_id', 'EMP001')->first();
    if (!$testEmployee) {
        $testEmployee = DB::table('employees')->first();
        echo "   Using first available employee: {$testEmployee->employee_id}\n";
    }
    
    if ($testEmployee) {
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
            ->first();
            
        if ($existing) {
            echo "   ✅ Test assignment already exists (ID: {$existing->upcoming_id})\n";
        } else {
            $insertId = DB::table('upcoming_trainings')->insertGetId($testData);
            echo "   ✅ Created test assignment (ID: $insertId)\n";
        }
    }
    
    // Final check
    echo "\n6. Final verification:\n";
    $finalRecords = DB::table('upcoming_trainings')->get();
    echo "   Total records now: " . $finalRecords->count() . "\n";
    
    if ($finalRecords->count() > 0) {
        echo "   Recent records:\n";
        foreach ($finalRecords->take(3) as $record) {
            echo "     - {$record->employee_id}: {$record->training_title} ({$record->status}) - Source: {$record->source}\n";
        }
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";
    echo "✅ Data should now appear in ESS portal\n";
    echo "🔄 Refresh the My Trainings page to see the data\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
