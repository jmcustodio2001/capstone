<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Checking potential_successors table structure:\n";
    $columns = DB::select('DESCRIBE potential_successors');
    
    foreach ($columns as $col) {
        echo "- " . $col->Field . " (" . $col->Type . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}