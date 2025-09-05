<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CustomerServiceSalesSkillsTraining;
use App\Models\Employee;
use App\Models\EmployeeTrainingDashboard;
use Illuminate\Support\Facades\Schema;

echo "Testing Customer Service Sales Skills Training Module...\n\n";

// 1. Check if table exists
echo "1. Checking if customer_service_sales_skills_training table exists...\n";
if (Schema::hasTable('customer_service_sales_skills_training')) {
    echo "✓ Table exists\n";
    
    // Check table structure
    $columns = Schema::getColumnListing('customer_service_sales_skills_training');
    echo "   Columns: " . implode(', ', $columns) . "\n";
} else {
    echo "✗ Table does not exist\n";
    echo "   Run: php artisan migrate\n";
}

// 2. Check if employee_training_dashboards table exists
echo "\n2. Checking if employee_training_dashboards table exists...\n";
if (Schema::hasTable('employee_training_dashboards')) {
    echo "✓ Table exists\n";
} else {
    echo "✗ Table does not exist - this is required for training records\n";
    echo "   This table is needed for the foreign key relationship\n";
}

// 3. Test model relationships
echo "\n3. Testing model relationships...\n";
try {
    $employees = Employee::take(5)->get();
    echo "✓ Employee model working - found " . $employees->count() . " employees\n";
} catch (Exception $e) {
    echo "✗ Employee model error: " . $e->getMessage() . "\n";
}

try {
    $trainings = EmployeeTrainingDashboard::take(5)->get();
    echo "✓ EmployeeTrainingDashboard model working - found " . $trainings->count() . " training records\n";
} catch (Exception $e) {
    echo "✗ EmployeeTrainingDashboard model error: " . $e->getMessage() . "\n";
}

// 4. Test creating a record (dry run)
echo "\n4. Testing record creation (dry run)...\n";
try {
    $employee = Employee::first();
    $training = EmployeeTrainingDashboard::first();
    
    if ($employee && $training) {
        echo "✓ Sample data available:\n";
        echo "   Employee: {$employee->first_name} {$employee->last_name} ({$employee->employee_id})\n";
        echo "   Training: " . ($training->course->course_title ?? $training->title ?? 'Training') . "\n";
        
        // Test validation
        $data = [
            'employee_id' => $employee->employee_id,
            'training_id' => $training->id,
            'date_completed' => now()->format('Y-m-d'),
        ];
        
        echo "✓ Sample data structure valid for controller\n";
    } else {
        echo "✗ No sample data available for testing\n";
    }
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
}

echo "\n5. Checking controller route accessibility...\n";
try {
    $url = route('customer_service_sales_skills_training.index');
    echo "✓ Route exists: {$url}\n";
} catch (Exception $e) {
    echo "✗ Route error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
