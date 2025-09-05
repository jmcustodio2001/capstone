<?php
// Direct test for competency gap saving issue

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use App\Http\Controllers\CompetencyGapAnalysisController;
use Illuminate\Support\Facades\DB;

echo "=== Direct Competency Gap Test ===\n";

try {
    // Test 1: Check database connection
    echo "1. Testing database connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "   ✅ Database connected\n";
    
    // Test 2: Check if competency_gaps table exists
    echo "2. Checking competency_gaps table...\n";
    $tableExists = DB::select("SHOW TABLES LIKE 'competency_gaps'");
    if (empty($tableExists)) {
        echo "   ❌ competency_gaps table does not exist!\n";
        
        // Create the table
        echo "   Creating competency_gaps table...\n";
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
                KEY competency_gaps_competency_id_foreign (competency_id),
                CONSTRAINT competency_gaps_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees (employee_id) ON DELETE CASCADE,
                CONSTRAINT competency_gaps_competency_id_foreign FOREIGN KEY (competency_id) REFERENCES competency_library (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "   ✅ competency_gaps table created\n";
    } else {
        echo "   ✅ competency_gaps table exists\n";
    }
    
    // Test 3: Check for sample data
    echo "3. Checking for sample data...\n";
    $employee = DB::table('employees')->first();
    $competency = DB::table('competency_library')->first();
    
    if (!$employee) {
        echo "   ❌ No employees found\n";
        return;
    }
    if (!$competency) {
        echo "   ❌ No competencies found\n";
        return;
    }
    
    echo "   ✅ Found employee: {$employee->employee_id}\n";
    echo "   ✅ Found competency: {$competency->id}\n";
    
    // Test 4: Test controller directly
    echo "4. Testing controller directly...\n";
    
    $controller = new CompetencyGapAnalysisController();
    
    // Create a mock request
    $requestData = [
        'employee_id' => $employee->employee_id,
        'competency_id' => $competency->id,
        'required_level' => 5,
        'current_level' => 2,
        'gap' => 3,
        'gap_description' => 'Test gap record',
        'expired_date' => null
    ];
    
    $request = new Request($requestData);
    
    try {
        $response = $controller->store($request);
        echo "   ✅ Controller store method executed\n";
        echo "   Response: " . $response->getContent() . "\n";
        
        // Check if record was created
        $gapCount = DB::table('competency_gaps')->count();
        echo "   Total gap records: {$gapCount}\n";
        
    } catch (Exception $e) {
        echo "   ❌ Controller error: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n=== Test completed ===\n";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
