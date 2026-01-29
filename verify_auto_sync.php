<?php

use Illuminate\Support\Facades\Http;
use App\Models\EmployeeCompetencyProfile;
use App\Models\CompetencyLibrary;

echo "=== EMPLOYEE SKILLS AUTO-SYNC VERIFICATION ===\n\n";

// 1. Fetch employees from API
echo "1. Fetching employees from API...\n";
$response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
$employees = $response->successful() ? $response->json() : [];

if (isset($employees['data']) && is_array($employees['data'])) {
    $employees = $employees['data'];
}

echo "   Found " . count($employees) . " employees\n\n";

// 2. Show employee skills
echo "2. Employee Skills from API:\n";
foreach ($employees as $emp) {
    $empId = $emp['employee_id'] ?? $emp['id'] ?? 'N/A';
    $name = ($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '');
    $skills = $emp['skills'] ?? 'N/A';
    
    echo "   - {$name} (ID: {$empId})\n";
    echo "     Skills: {$skills}\n\n";
}

// 3. Check competency profiles
echo "3. Competency Profiles in Database:\n";
$profiles = EmployeeCompetencyProfile::with(['competency'])->get();
$groupedProfiles = $profiles->groupBy('employee_id');

foreach ($groupedProfiles as $employeeId => $employeeProfiles) {
    echo "   Employee ID: {$employeeId}\n";
    echo "   Competencies (" . $employeeProfiles->count() . "):\n";
    
    foreach ($employeeProfiles as $profile) {
        $competencyName = $profile->competency->competency_name ?? 'Unknown';
        $proficiency = $profile->proficiency_level;
        echo "     - {$competencyName} (Proficiency: {$proficiency}/5)\n";
    }
    echo "\n";
}

echo "=== VERIFICATION COMPLETE ===\n";
echo "\n✅ If you see competencies matching employee skills, AUTO-SYNC IS WORKING!\n";
