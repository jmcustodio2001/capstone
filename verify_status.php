<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\EmployeeCompetencyProfile;

$response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
$apiData = $response->successful() ? $response->json() : [];
$employees = $apiData['data'] ?? $apiData;

echo "--- Current Status ---\n";
foreach ($employees as $employee) {
    $empId = $employee['employee_id'] ?? $employee['id'] ?? null;
    $name = ($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '');
    
    $profiles = EmployeeCompetencyProfile::where('employee_id', $empId)->with('competency')->get();
    
    echo "Employee: $name (ID: $empId)\n";
    echo "  Skills field: " . ($employee['skills'] ?? 'EMPTY') . "\n";
    echo "  Competencies in DB: " . $profiles->count() . "\n";
    foreach($profiles as $p) {
        echo "    - " . ($p->competency ? $p->competency->competency_name : 'N/A') . "\n";
    }
    echo "\n";
}
echo "--- Done ---\n";
