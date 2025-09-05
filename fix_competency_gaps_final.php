<?php
require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== Competency Gaps Table Fix ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Check database connection
    echo "1. Testing database connection...\n";
    $pdo = DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    echo "✅ Connected to database: {$dbName}\n\n";

    // Drop existing table if it exists
    echo "2. Dropping existing competency_gaps table (if exists)...\n";
    Schema::dropIfExists('competency_gaps');
    echo "✅ Existing table dropped\n";

    // Create competency_gaps table
    echo "\n3. Creating competency_gaps table...\n";
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

        // Add indexes for better performance
        $table->index('employee_id');
        $table->index('competency_id');
        $table->index('is_active');
    });
    echo "✅ Table created successfully\n";

    // Verify table structure
    echo "\n4. Verifying table structure...\n";
    $columns = DB::select("DESCRIBE competency_gaps");
    foreach ($columns as $column) {
        echo "- {$column->Field}: {$column->Type}\n";
    }

    // Check for related data
    echo "\n5. Checking related tables...\n";
    $employeeCount = DB::table('employees')->count();
    $competencyCount = DB::table('competency_library')->count();
    echo "Employees: {$employeeCount}\n";
    echo "Competencies: {$competencyCount}\n";

    // Test operations if data exists
    if ($employeeCount > 0 && $competencyCount > 0) {
        echo "\n6. Testing insert operations...\n";
        
        $employee = DB::table('employees')->first();
        $competency = DB::table('competency_library')->first();
        
        echo "Using Employee: {$employee->first_name} {$employee->last_name} (ID: {$employee->employee_id})\n";
        echo "Using Competency: {$competency->competency_name} (ID: {$competency->id})\n";

        // Test direct insert
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
        
        echo "✅ Test insert successful! ID: {$testId}\n";
        
        // Verify record exists
        $record = DB::table('competency_gaps')->where('id', $testId)->first();
        if ($record) {
            echo "✅ Record verified in database\n";
        }
        
        // Clean up test record
        DB::table('competency_gaps')->where('id', $testId)->delete();
        echo "✅ Test record cleaned up\n";
    } else {
        echo "\n⚠️ Cannot test inserts - missing data:\n";
        if ($employeeCount == 0) echo "- No employees found\n";
        if ($competencyCount == 0) echo "- No competencies found\n";
    }

    echo "\n=== Fix completed successfully! ===\n";
    echo "The competency_gaps table is now ready for use.\n";
    echo "Try adding a competency gap record through the web interface.\n";
    echo "Check Laravel logs at storage/logs/laravel.log for detailed debugging info.\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
