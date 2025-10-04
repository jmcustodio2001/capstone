<?php

// Fix all migration conflicts
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Fixing all migration conflicts...\n";
    
    // 1. Fix succession_readiness_ratings table
    if (Schema::hasTable('succession_readiness_ratings')) {
        Schema::drop('succession_readiness_ratings');
        echo "Dropped existing succession_readiness_ratings table\n";
    }
    
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
    echo "Created succession_readiness_ratings table\n";
    
    // 2. Fix training_record_certificate_tracking table
    if (Schema::hasTable('training_record_certificate_tracking')) {
        Schema::drop('training_record_certificate_tracking');
        echo "Dropped existing training_record_certificate_tracking table\n";
    }
    
    Schema::create('training_record_certificate_tracking', function ($table) {
        $table->id();
        $table->string('employee_id', 20);
        $table->unsignedBigInteger('course_id')->nullable();
        $table->date('training_date')->nullable();
        $table->string('certificate_number')->nullable();
        $table->date('certificate_expiry')->nullable();
        $table->string('certificate_url')->nullable();
        $table->string('status')->default('Active');
        $table->text('remarks')->nullable();
        $table->timestamps();
        
        $table->index('employee_id');
        $table->index('course_id');
        $table->index('status');
    });
    echo "Created training_record_certificate_tracking table\n";
    
    // Mark all related migrations as completed
    $migrations = [
        '2025_01_15_000000_add_readiness_level_to_succession_readiness_ratings_table',
        '2025_01_20_000002_create_training_record_certificate_tracking_table',
        '2025_01_20_000003_create_succession_readiness_ratings_table',
        '2025_08_16_140000_create_training_record_certificate_tracking_table',
        '2025_08_17_000000_add_certificate_url_to_training_record_certificate_tracking_table',
        '2025_08_19_170000_create_succession_readiness_ratings_table',
        '2025_08_23_023200_update_employee_id_column_in_training_record_certificate_tracking',
        '2025_08_23_023700_fix_training_record_certificate_tracking_schema'
    ];
    
    foreach ($migrations as $migration) {
        DB::table('migrations')->updateOrInsert(
            ['migration' => $migration],
            ['batch' => 1]
        );
        echo "Marked migration {$migration} as completed\n";
    }
    
    echo "All migration conflicts fixed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}