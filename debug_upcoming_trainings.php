<?php

// Debug script to check upcoming trainings data
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UpcomingTraining;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DEBUGGING UPCOMING TRAININGS ===\n\n";

// 1. Check if table exists
echo "1. Checking if upcoming_trainings table exists...\n";
if (Schema::hasTable('upcoming_trainings')) {
    echo "✅ Table exists\n\n";
    
    // 2. Check table structure
    echo "2. Table structure:\n";
    $columns = Schema::getColumnListing('upcoming_trainings');
    foreach ($columns as $column) {
        echo "   - $column\n";
    }
    echo "\n";
    
    // 3. Check total records
    $totalRecords = DB::table('upcoming_trainings')->count();
    echo "3. Total records in table: $totalRecords\n\n";
    
    if ($totalRecords > 0) {
        // 4. Show sample records
        echo "4. Sample records:\n";
        $records = DB::table('upcoming_trainings')->take(5)->get();
        foreach ($records as $record) {
            echo "   ID: {$record->upcoming_id}, Employee: {$record->employee_id}, Title: {$record->training_title}, Status: {$record->status}, Source: " . ($record->source ?? 'NULL') . "\n";
        }
        echo "\n";
        
        // 5. Check for specific employee (get first employee from employees table)
        echo "5. Checking for specific employee records...\n";
        $firstEmployee = DB::table('employees')->first();
        if ($firstEmployee) {
            echo "   Testing with employee: {$firstEmployee->employee_id} ({$firstEmployee->first_name} {$firstEmployee->last_name})\n";
            $employeeRecords = DB::table('upcoming_trainings')
                ->where('employee_id', $firstEmployee->employee_id)
                ->get();
            echo "   Records for this employee: " . $employeeRecords->count() . "\n";
            
            if ($employeeRecords->count() > 0) {
                foreach ($employeeRecords as $record) {
                    echo "     - {$record->training_title} (Status: {$record->status}, Source: " . ($record->source ?? 'NULL') . ")\n";
                }
            }
        }
        echo "\n";
    }
    
    // 6. Test the MyTrainingController logic
    echo "6. Testing MyTrainingController logic...\n";
    try {
        $testEmployeeId = DB::table('employees')->value('employee_id');
        if ($testEmployeeId) {
            echo "   Testing with employee ID: $testEmployeeId\n";
            
            // Simulate the controller logic
            $manualUpcoming = UpcomingTraining::where('employee_id', $testEmployeeId)->get();
            echo "   Manual upcoming trainings found: " . $manualUpcoming->count() . "\n";
            
            if ($manualUpcoming->count() > 0) {
                foreach ($manualUpcoming as $training) {
                    echo "     - {$training->training_title} (Status: {$training->status})\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "   Error testing controller logic: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ Table does not exist!\n";
    echo "The upcoming_trainings table needs to be created.\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
