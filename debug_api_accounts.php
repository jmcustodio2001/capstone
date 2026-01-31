<?php

use Illuminate\Support\Facades\Http;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$response = Http::get('http://hr4.jetlougetravels-ph.com/api/accounts');
$data = $response->json();
$essAccounts = $data['data']['ess_accounts'] ?? $data['ess_accounts'] ?? [];

foreach ($essAccounts as $account) {
    if (isset($account['employee']['email']) && $account['employee']['email'] === 'j.mcustodio2001@gmail.com') {
        echo "Found Account for j.mcustodio2001@gmail.com:\n";
        print_r($account['employee']);
        // Also check if there's a profile picture in the account object itself or nested differently
        if (isset($account['profile_picture'])) {
             echo "Profile picture found in account root: " . $account['profile_picture'] . "\n";
        }
        break;
    }
}
