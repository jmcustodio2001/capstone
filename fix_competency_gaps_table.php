<?php
// Fix script for competency_gaps table issues
// Similar to the fix scripts created for other missing tables in the HR2ESS system

require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== Competency Gaps Table Fix Script ===\n";
echo "Checking and fixing competency_gaps table...\n\n";

try {
    // Check if table exists
    if (!Schema::hasTable('competency_gaps')) {
        echo "❌ competency_gaps table does not exist. Creating...\n";
        
        Schema::create('competency_gaps', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->unsignedBigInteger('competency_id');
            $table->integer('required_level');
            $table->integer('current_level');
            $table->integer('gap');
            $table->text('gap_description')->nullable();
            $table->timestamp('expired_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')
                ->onDelete('cascade');

            $table->foreign('competency_id')
                ->references('id')
                ->on('competency_library')
                ->onDelete('cascade');
        });
        
        echo "✅ competency_gaps table created successfully!\n";
    } else {
        echo "✅ competency_gaps table exists\n";
        
        // Check for missing columns
        $missingColumns = [];
        
        if (!Schema::hasColumn('competency_gaps', 'expired_date')) {
            $missingColumns[] = 'expired_date';
        }
        
        if (!Schema::hasColumn('competency_gaps', 'is_active')) {
            $missingColumns[] = 'is_active';
        }
        
        if (!empty($missingColumns)) {
            echo "Adding missing columns: " . implode(', ', $missingColumns) . "\n";
            
            Schema::table('competency_gaps', function (Blueprint $table) use ($missingColumns) {
                if (in_array('expired_date', $missingColumns)) {
                    $table->timestamp('expired_date')->nullable()->after('gap_description');
                }
                if (in_array('is_active', $missingColumns)) {
                    $table->boolean('is_active')->default(true)->after('expired_date');
                }
            });
            
            echo "✅ Missing columns added successfully!\n";
        }
    }
    
    // Test basic operations
    echo "\nTesting basic operations...\n";
    
    // Test if we can query the table
    $count = DB::table('competency_gaps')->count();
    echo "Current records in competency_gaps: {$count}\n";
    
    // Check if we have employees and competencies to work with
    $employeeCount = DB::table('employees')->count();
    $competencyCount = DB::table('competency_library')->count();
    
    echo "Available employees: {$employeeCount}\n";
    echo "Available competencies: {$competencyCount}\n";
    
    if ($employeeCount > 0 && $competencyCount > 0) {
        // Get first employee and competency for testing
        $employee = DB::table('employees')->first();
        $competency = DB::table('competency_library')->first();
        
        echo "\nTesting insert operation...\n";
        echo "Using Employee ID: {$employee->employee_id}\n";
        echo "Using Competency ID: {$competency->id}\n";
        
        // Test insert
        $testId = DB::table('competency_gaps')->insertGetId([
            'employee_id' => $employee->employee_id,
            'competency_id' => $competency->id,
            'required_level' => 5,
            'current_level' => 2,
            'gap' => 3,
            'gap_description' => 'Test record - will be deleted',
            'expired_date' => now()->addDays(30),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ Test record inserted with ID: {$testId}\n";
        
        // Clean up test record
        DB::table('competency_gaps')->where('id', $testId)->delete();
        echo "✅ Test record cleaned up\n";
    }
    
    echo "\n=== Fix completed successfully! ===\n";
    echo "The competency_gaps table is now ready for use.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Additional debugging
    echo "\nDebugging information:\n";
    echo "Database connection: " . (DB::connection()->getPdo() ? 'OK' : 'Failed') . "\n";
    
    try {
        $tables = DB::select("SHOW TABLES LIKE 'competency_gaps'");
        echo "Table exists check: " . (count($tables) > 0 ? 'YES' : 'NO') . "\n";
    } catch (Exception $e2) {
        echo "Table check failed: " . $e2->getMessage() . "\n";
    }
}
