<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$constraints = DB::select("
    SELECT
        TABLE_NAME,
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'trainings'
    AND REFERENCED_TABLE_SCHEMA = DATABASE()
");

foreach ($constraints as $constraint) {
    echo "Table: {$constraint->TABLE_NAME}, Constraint: {$constraint->CONSTRAINT_NAME}\n";
}
