<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$response = Http::get('http://hr4.jetlougetravels-ph.com/api/employees');
echo "Status: " . $response->status() . "\n";
$data = $response->json();
echo "Data structure keys: " . implode(', ', array_keys((array)$data)) . "\n";
if (isset($data['data'])) {
    echo "Count in data['data']: " . count($data['data']) . "\n";
    foreach($data['data'] as $e) {
        echo "- " . ($e['first_name'] ?? '??') . " " . ($e['last_name'] ?? '??') . "\n";
    }
} else {
    echo "Count in data: " . count($data) . "\n";
}
