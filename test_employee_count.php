<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;

echo "Total Employees in Database: " . Employee::count() . "\n";
echo "Sample Employees:\n";

Employee::latest()->take(5)->get()->each(function($emp) {
    echo " - " . $emp->employee_id . ": " . $emp->first_name . " " . $emp->last_name . "\n";
});
