<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employees = App\Models\Employee::all();
echo "Found " . $employees->count() . " local employees:\n";
foreach ($employees as $e) {
    echo "ID: {$e->id} | EmpID: {$e->employee_id} | Name: {$e->first_name} {$e->last_name} | Skills: {$e->skills}\n";
}
