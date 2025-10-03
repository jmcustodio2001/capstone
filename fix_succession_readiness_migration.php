<?php

// Fix succession readiness ratings migration conflict
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Fixing succession_readiness_ratings migration conflict...\n";
    
    // Drop the table if it exists
    if (Schema::hasTable('succession_readiness_ratings')) {
        Schema::drop('succession_readiness_ratings');
        echo "Dropped existing succession_readiness_ratings table\n";
    }
    
    // Create the table with the correct structure
    Schema::create('succession_readiness_ratings', function ($table) {
        $table->id();
        $table->string('employee_id', 20);
        $table->integer('readiness_score')->default(0);
        $table->string('readiness_level')->nullable();
        $table->text('assessment_notes')->nullable();
        $table->date('assessment_date')->nullable();
        $table->string('assessed_by')->nullable();
        $table->timestamps();
        
        $table->index('employee_id');
        $table->index('readiness_score');
    });
    
    echo "Created succession_readiness_ratings table successfully\n";
    
    // Mark all related migrations as run
    $migrations = [
        '2025_01_15_000000_add_readiness_level_to_succession_readiness_ratings_table',
        '2025_01_20_000003_create_succession_readiness_ratings_table',
        '2025_08_19_170000_create_succession_readiness_ratings_table'
    ];
    
    foreach ($migrations as $migration) {
        DB::table('migrations')->updateOrInsert(
            ['migration' => $migration],
            ['batch' => 1]
        );
        echo "Marked migration {$migration} as completed\n";
    }
    
    echo "Migration fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}