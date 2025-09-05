<?php
// Debug script for competency gaps table and save functionality
require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\CompetencyGap;
use App\Models\Employee;
use App\Models\CompetencyLibrary;

echo "=== Competency Gaps Debug Script ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. Check database connection
    echo "1. Testing database connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connected successfully\n";
    echo "Database name: " . DB::connection()->getDatabaseName() . "\n\n";

    // 2. Check if competency_gaps table exists
    echo "2. Checking competency_gaps table...\n";
    $tableExists = Schema::hasTable('competency_gaps');
    echo "Table exists: " . ($tableExists ? "✅ YES" : "❌ NO") . "\n";

    if (!$tableExists) {
        echo "Creating competency_gaps table...\n";
        DB::statement("
            CREATE TABLE IF NOT EXISTS competency_gaps (
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
        echo "✅ Table created\n";
    }

    // 3. Check table structure
    echo "\n3. Checking table structure...\n";
    $columns = DB::select("DESCRIBE competency_gaps");
    foreach ($columns as $column) {
        echo "- {$column->Field}: {$column->Type} " . ($column->Null == 'YES' ? '(nullable)' : '(required)') . "\n";
    }

    // 4. Check foreign key constraints
    echo "\n4. Checking foreign key constraints...\n";
    try {
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'competency_gaps' 
            AND TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (empty($constraints)) {
            echo "⚠️ No foreign key constraints found. Adding them...\n";
            
            // Add foreign key constraints
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
        } else {
            echo "✅ Foreign key constraints exist:\n";
            foreach ($constraints as $constraint) {
                echo "- {$constraint->CONSTRAINT_NAME}: {$constraint->COLUMN_NAME} -> {$constraint->REFERENCED_TABLE_NAME}.{$constraint->REFERENCED_COLUMN_NAME}\n";
            }
        }
    } catch (Exception $e) {
        echo "⚠️ Could not check constraints: " . $e->getMessage() . "\n";
    }

    // 5. Check related tables
    echo "\n5. Checking related tables...\n";
    $employeeCount = DB::table('employees')->count();
    $competencyCount = DB::table('competency_library')->count();
    echo "Employees: {$employeeCount}\n";
    echo "Competencies: {$competencyCount}\n";

    if ($employeeCount == 0) {
        echo "❌ No employees found! This will cause foreign key constraint failures.\n";
    }
    if ($competencyCount == 0) {
        echo "❌ No competencies found! This will cause foreign key constraint failures.\n";
    }

    // 6. Test insert operation
    if ($employeeCount > 0 && $competencyCount > 0) {
        echo "\n6. Testing insert operation...\n";
        
        $employee = DB::table('employees')->first();
        $competency = DB::table('competency_library')->first();
        
        echo "Using Employee ID: {$employee->employee_id}\n";
        echo "Using Competency: {$competency->competency_name} (ID: {$competency->id})\n";

        // Test direct DB insert
        try {
            $testId = DB::table('competency_gaps')->insertGetId([
                'employee_id' => $employee->employee_id,
                'competency_id' => $competency->id,
                'required_level' => 5,
                'current_level' => 2,
                'gap' => 3,
                'gap_description' => 'Debug test record',
                'expired_date' => now()->addDays(30),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✅ Direct DB insert successful! ID: {$testId}\n";
            
            // Clean up
            DB::table('competency_gaps')->where('id', $testId)->delete();
            echo "✅ Test record cleaned up\n";
        } catch (Exception $e) {
            echo "❌ Direct DB insert failed: " . $e->getMessage() . "\n";
        }

        // Test Eloquent model insert
        try {
            echo "\n7. Testing Eloquent model insert...\n";
            $gap = CompetencyGap::create([
                'employee_id' => $employee->employee_id,
                'competency_id' => $competency->id,
                'required_level' => 4,
                'current_level' => 1,
                'gap' => 3,
                'gap_description' => 'Eloquent test record',
                'expired_date' => now()->addDays(30),
                'is_active' => true
            ]);
            echo "✅ Eloquent insert successful! ID: {$gap->id}\n";
            
            // Clean up
            $gap->delete();
            echo "✅ Eloquent test record cleaned up\n";
        } catch (Exception $e) {
            echo "❌ Eloquent insert failed: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    // 8. Check current records
    echo "\n8. Current competency_gaps records:\n";
    $currentCount = DB::table('competency_gaps')->count();
    echo "Total records: {$currentCount}\n";
    
    if ($currentCount > 0) {
        $records = DB::table('competency_gaps')
            ->join('employees', 'competency_gaps.employee_id', '=', 'employees.employee_id')
            ->join('competency_library', 'competency_gaps.competency_id', '=', 'competency_library.id')
            ->select('competency_gaps.*', 'employees.first_name', 'employees.last_name', 'competency_library.competency_name')
            ->limit(5)
            ->get();
            
        foreach ($records as $record) {
            echo "- ID {$record->id}: {$record->first_name} {$record->last_name} - {$record->competency_name} (Gap: {$record->gap})\n";
        }
    }

    echo "\n=== Debug completed successfully! ===\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
