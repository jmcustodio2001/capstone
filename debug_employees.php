<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$destinations = \App\Models\DestinationKnowledgeTraining::all();
foreach ($destinations as $d) {
    echo "ID: {$d->id}, Employee ID: '{$d->employee_id}', Employee Name: " . ($d->employee ? $d->employee->first_name . ' ' . $d->employee->last_name : 'NULL') . "\n";
}

$employees = \App\Models\Employee::all();
echo "\nLocal Employees:\n";
foreach ($employees as $e) {
    echo "Employee ID: '{$e->employee_id}', Name: {$e->first_name} {$e->last_name}\n";
}
