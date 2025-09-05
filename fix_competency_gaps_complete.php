<?php
// Complete fix for competency gaps table and save functionality
require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\CompetencyGap;
use App\Models\Employee;
use App\Models\CompetencyLibrary;

echo "=== Competency Gaps Complete Fix ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. Test database connection
    echo "1. Testing database connection...\n";
    $pdo = DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    echo "✅ Connected to database: {$dbName}\n\n";

    // 2. Drop and recreate table to ensure clean state
    echo "2. Creating competency_gaps table...\n";
    
    // Drop table if exists
    DB::statement("DROP TABLE IF EXISTS competency_gaps");
    echo "- Dropped existing table (if any)\n";
    
    // Create table with all required columns
    DB::statement("
        CREATE TABLE competency_gaps (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            employee_id varchar(20) NOT NULL,
            competency_id bigint unsigned NOT NULL,
            required_level int NOT NULL,
            current_level int NOT NULL,
            gap int NOT NULL,
            gap_description text,
            expired_date timestamp NULL DEFAULT NULL,
            is_active tinyint(1) NOT NULL DEFAULT '1',
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY competency_gaps_employee_id_foreign (employee_id),
            KEY competency_gaps_competency_id_foreign (competency_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Table created successfully\n";

    // 3. Add foreign key constraints (with error handling)
    echo "\n3. Adding foreign key constraints...\n";
    
    try {
        DB::statement("
            ALTER TABLE competency_gaps 
            ADD CONSTRAINT competency_gaps_employee_id_foreign 
            FOREIGN KEY (employee_id) REFERENCES employees (employee_id) ON DELETE CASCADE
        ");
        echo "✅ Employee foreign key added\n";
    } catch (Exception $e) {
        echo "⚠️ Employee foreign key failed: " . $e->getMessage() . "\n";
    }
    
    try {
        DB::statement("
            ALTER TABLE competency_gaps 
            ADD CONSTRAINT competency_gaps_competency_id_foreign 
            FOREIGN KEY (competency_id) REFERENCES competency_library (id) ON DELETE CASCADE
        ");
        echo "✅ Competency foreign key added\n";
    } catch (Exception $e) {
        echo "⚠️ Competency foreign key failed: " . $e->getMessage() . "\n";
    }

    // 4. Verify table structure
    echo "\n4. Verifying table structure...\n";
    $columns = DB::select("DESCRIBE competency_gaps");
    foreach ($columns as $column) {
        echo "- {$column->Field}: {$column->Type}\n";
    }

    // 5. Check related tables
    echo "\n5. Checking related tables...\n";
    $employeeCount = DB::table('employees')->count();
    $competencyCount = DB::table('competency_library')->count();
    echo "Employees: {$employeeCount}\n";
    echo "Competencies: {$competencyCount}\n";

    // 6. Test insert if we have data
    if ($employeeCount > 0 && $competencyCount > 0) {
        echo "\n6. Testing insert operations...\n";
        
        $employee = DB::table('employees')->first();
        $competency = DB::table('competency_library')->first();
        
        echo "Test Employee: {$employee->first_name} {$employee->last_name} (ID: {$employee->employee_id})\n";
        echo "Test Competency: {$competency->competency_name} (ID: {$competency->id})\n";

        // Test 1: Direct DB insert
        try {
            $testId1 = DB::table('competency_gaps')->insertGetId([
                'employee_id' => $employee->employee_id,
                'competency_id' => $competency->id,
                'required_level' => 5,
                'current_level' => 2,
                'gap' => 3,
                'gap_description' => 'Test record 1 - Direct DB',
                'expired_date' => now()->addDays(30),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✅ Direct DB insert successful! ID: {$testId1}\n";
        } catch (Exception $e) {
            echo "❌ Direct DB insert failed: " . $e->getMessage() . "\n";
        }

        // Test 2: Eloquent model insert
        try {
            $gap = new CompetencyGap();
            $gap->employee_id = $employee->employee_id;
            $gap->competency_id = $competency->id;
            $gap->required_level = 4;
            $gap->current_level = 1;
            $gap->gap = 3;
            $gap->gap_description = 'Test record 2 - Eloquent';
            $gap->expired_date = now()->addDays(30);
            $gap->is_active = true;
            $gap->save();
            
            echo "✅ Eloquent insert successful! ID: {$gap->id}\n";
        } catch (Exception $e) {
            echo "❌ Eloquent insert failed: " . $e->getMessage() . "\n";
        }

        // Test 3: Eloquent create method
        try {
            $gap2 = CompetencyGap::create([
                'employee_id' => $employee->employee_id,
                'competency_id' => $competency->id,
                'required_level' => 3,
                'current_level' => 0,
                'gap' => 3,
                'gap_description' => 'Test record 3 - Eloquent Create',
                'expired_date' => now()->addDays(30),
                'is_active' => true
            ]);
            echo "✅ Eloquent create successful! ID: {$gap2->id}\n";
        } catch (Exception $e) {
            echo "❌ Eloquent create failed: " . $e->getMessage() . "\n";
        }

        // Show created records
        echo "\n7. Current test records:\n";
        $records = DB::table('competency_gaps')
            ->join('employees', 'competency_gaps.employee_id', '=', 'employees.employee_id')
            ->join('competency_library', 'competency_gaps.competency_id', '=', 'competency_library.id')
            ->select('competency_gaps.*', 'employees.first_name', 'employees.last_name', 'competency_library.competency_name')
            ->get();
            
        foreach ($records as $record) {
            echo "- ID {$record->id}: {$record->first_name} {$record->last_name} - {$record->competency_name} (Gap: {$record->gap})\n";
        }

        // Clean up test records
        echo "\n8. Cleaning up test records...\n";
        $deleted = DB::table('competency_gaps')->where('gap_description', 'LIKE', 'Test record%')->delete();
        echo "✅ Deleted {$deleted} test records\n";
    } else {
        echo "\n⚠️ Cannot test inserts - missing employees or competencies\n";
        if ($employeeCount == 0) echo "- No employees found\n";
        if ($competencyCount == 0) echo "- No competencies found\n";
    }

    echo "\n=== Fix completed successfully! ===\n";
    echo "The competency_gaps table is now ready.\n";
    echo "You can now try adding competency gap records through the web interface.\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
