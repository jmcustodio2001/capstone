<?php
// Quick fix to update employee IDs to numeric values
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

try {
    // Fetch employees from API
    $response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
    $employees = $response->successful() ? $response->json() : [];

    if (isset($employees['data'])) {
        $employees = $employees['data'];
    }

    if (!is_array($employees)) {
        die("Failed to fetch employees from API\n");
    }

    $updated = 0;
    $skipped = 0;

    foreach ($employees as $emp) {
        $apiId = $emp['id'] ?? $emp['employee_id'] ?? null;
        $email = $emp['email'] ?? null;

        if (!$apiId || !$email) {
            continue;
        }

        // Find employee by email and update ID
        $employee = DB::table('employees')
            ->where('email', $email)
            ->first();

        if ($employee) {
            // Update to numeric ID from API
            DB::table('employees')
                ->where('email', $email)
                ->update(['employee_id' => $apiId]);

            echo "✓ Updated {$email}: {$employee->employee_id} → {$apiId}\n";
            $updated++;
        } else {
            echo "✗ Not found: {$email}\n";
            $skipped++;
        }
    }

    echo "\n✓ Update complete! Updated: {$updated}, Skipped: {$skipped}\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
