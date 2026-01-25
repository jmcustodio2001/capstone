<?php
// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyLibrary;

try {
    $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
    $employees = $response->successful() ? $response->json() : [];

    if (isset($employees['data'])) {
        $employees = $employees['data'];
    }

    echo "Checking " . count($employees) . " employees...\n";

    foreach ($employees as $employee) {
        $empId = $employee['employee_id'] ?? $employee['id'] ?? null;
        if (!$empId) continue;

        $fullName = ($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '');
        $skillsString = $employee['skills'] ?? '';
        
        // Parse current skills from API
        $apiSkills = preg_split('/[\r\n,;]+/', $skillsString, -1, PREG_SPLIT_NO_EMPTY);
        $apiSkills = array_map(function($s) { return trim(ucwords(strtolower($s))); }, $apiSkills);
        $apiSkills = array_filter($apiSkills);

        // Get local competency profiles
        $profiles = EmployeeCompetencyProfile::where('employee_id', $empId)->with('competency')->get();
        
        $toDelete = [];
        foreach ($profiles as $profile) {
            $compName = $profile->competency ? $profile->competency->competency_name : null;
            if ($compName && !in_array($compName, $apiSkills)) {
                $toDelete[] = $profile->id;
            }
        }

        if (count($toDelete) > 0) {
            echo "Employee: $fullName ($empId)\n";
            echo "  API Skills: " . implode(', ', $apiSkills) . "\n";
            echo "  Local Competencies being removed: " . count($toDelete) . "\n";
            foreach($toDelete as $id) {
                // Find and delete
                $p = EmployeeCompetencyProfile::find($id);
                if ($p) {
                    echo "    - Removing: " . ($p->competency ? $p->competency->competency_name : 'Unknown') . "\n";
                    $p->delete();
                }
            }
        }
    }
    echo "\nCleanup Complete.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
