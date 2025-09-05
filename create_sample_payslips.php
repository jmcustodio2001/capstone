<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'hr2system',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Creating sample payslip data...\n";
    
    // Get some employee IDs from the employees table
    $employees = Capsule::table('employees')->select('employee_id')->limit(5)->get();
    
    if ($employees->isEmpty()) {
        echo "No employees found in the database. Please add employees first.\n";
        exit;
    }
    
    // Check if payslips table exists
    if (!Capsule::schema()->hasTable('payslips')) {
        echo "Payslips table does not exist. Running migration...\n";
        
        Capsule::schema()->create('payslips', function ($table) {
            $table->bigIncrements('id');
            $table->string('employee_id', 20);
            $table->string('pay_period');
            $table->decimal('basic_pay', 10, 2);
            $table->decimal('allowances', 10, 2)->nullable();
            $table->decimal('deductions', 10, 2)->nullable();
            $table->decimal('net_pay', 10, 2);
            $table->date('release_date');
            $table->string('status')->default('Released');
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
        
        echo "Payslips table created successfully.\n";
    }
    
    // Clear existing payslips for clean test data
    Capsule::table('payslips')->truncate();
    
    $payPeriods = [
        'January 2024',
        'February 2024', 
        'March 2024',
        'April 2024',
        'May 2024',
        'June 2024',
        'July 2024',
        'August 2024',
        'September 2024'
    ];
    
    $samplePayslips = [];
    
    foreach ($employees as $employee) {
        foreach ($payPeriods as $index => $period) {
            $basicPay = rand(15000, 25000);
            $allowances = rand(2000, 5000);
            $deductions = rand(1500, 3500);
            $netPay = $basicPay + $allowances - $deductions;
            
            $samplePayslips[] = [
                'employee_id' => $employee->employee_id,
                'pay_period' => $period,
                'basic_pay' => $basicPay,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'net_pay' => $netPay,
                'release_date' => date('Y-m-d', strtotime('2024-' . sprintf('%02d', $index + 1) . '-15')),
                'status' => 'Released',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }
    
    // Insert sample data
    Capsule::table('payslips')->insert($samplePayslips);
    
    $totalRecords = count($samplePayslips);
    echo "Successfully created {$totalRecords} sample payslip records.\n";
    
    // Display summary
    echo "\nSample data summary:\n";
    echo "Employees with payslips: " . $employees->count() . "\n";
    echo "Pay periods per employee: " . count($payPeriods) . "\n";
    echo "Total payslip records: {$totalRecords}\n";
    
    echo "\nSample payslips created successfully!\n";
    echo "You can now test the payslip access functionality.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

function now() {
    return date('Y-m-d H:i:s');
}
