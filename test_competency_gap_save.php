<?php
// Simple test script to debug competency gap saving issue
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\CompetencyGap;
use App\Models\Employee;
use App\Models\CompetencyLibrary;

try {
    // Test database connection
    echo "Testing database connection...\n";
    $connection = DB::connection()->getPdo();
    echo "✅ Database connected successfully\n";
    
    // Check if competency_gaps table exists
    echo "\nChecking competency_gaps table...\n";
    $tables = DB::select("SHOW TABLES LIKE 'competency_gaps'");
    if (empty($tables)) {
        echo "❌ competency_gaps table does not exist!\n";
        exit(1);
    }
    echo "✅ competency_gaps table exists\n";
    
    // Check table structure
    echo "\nChecking table structure...\n";
    $columns = DB::select("DESCRIBE competency_gaps");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    // Check if we have employees and competencies
    $employeeCount = Employee::count();
    $competencyCount = CompetencyLibrary::count();
    echo "\nData check:\n";
    echo "- Employees: {$employeeCount}\n";
    echo "- Competencies: {$competencyCount}\n";
    
    if ($employeeCount == 0) {
        echo "❌ No employees found in database\n";
    }
    
    if ($competencyCount == 0) {
        echo "❌ No competencies found in database\n";
    }
    
    // Test creating a competency gap record
    if ($employeeCount > 0 && $competencyCount > 0) {
        echo "\nTesting competency gap creation...\n";
        
        $employee = Employee::first();
        $competency = CompetencyLibrary::first();
        
        echo "Using Employee ID: {$employee->employee_id}\n";
        echo "Using Competency ID: {$competency->id}\n";
        
        $testData = [
            'employee_id' => $employee->employee_id,
            'competency_id' => $competency->id,
            'required_level' => 5,
            'current_level' => 2,
            'gap' => 3,
            'gap_description' => 'Test gap record',
            'expired_date' => now()->addDays(30)
        ];
        
        try {
            $gap = CompetencyGap::create($testData);
            echo "✅ Test competency gap created successfully with ID: {$gap->id}\n";
            
            // Clean up test record
            $gap->delete();
            echo "✅ Test record cleaned up\n";
            
        } catch (Exception $e) {
            echo "❌ Error creating competency gap: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
