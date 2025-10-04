<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$constraints = DB::select("
    SELECT
        TABLE_NAME,
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'trainings'
    AND REFERENCED_TABLE_SCHEMA = DATABASE()
    AND CONSTRAINT_NAME != 'PRIMARY'
    AND TABLE_NAME != 'trainings'
");

foreach ($constraints as $constraint) {
    echo sprintf(
        "Table: %s, Constraint: %s, Column: %s, References: %s\n",
        $constraint->TABLE_NAME,
        $constraint->CONSTRAINT_NAME,
        $constraint->COLUMN_NAME,
        $constraint->REFERENCED_TABLE_NAME
    );
}
