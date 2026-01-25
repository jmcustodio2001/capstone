<?php
// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$profiles = App\Models\EmployeeCompetencyProfile::where('employee_id', 2)->orWhere('employee_id', '2')->with('competency')->get();

echo "Found count: " . $profiles->count() . "\n";
foreach ($profiles as $p) {
    $cn = $p->competency ? $p->competency->competency_name : 'N/A';
    echo "ID: " . $p->id . " | Skill: " . $cn . " | Date: " . $p->created_at . "\n";
}
echo "---END---\n";
