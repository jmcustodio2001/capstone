<?php

use Illuminate\Support\Facades\Http;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
$data = $response->json();
$employees = $data['data'] ?? $data;

foreach ($employees as $emp) {
    $id = $emp['employee_id'] ?? $emp['id'] ?? $emp['external_employee_id'] ?? null;
    if ((string)$id === '2') {
        echo "Found Employee ID 2:\n";
        print_r($emp);
        break;
    }
}
