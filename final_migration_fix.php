<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

try {
    echo "Final migration fix - ensuring all tables exist...\n";
    
    // 1. Create succession_readiness_ratings table
    if (!Schema::hasTable('succession_readiness_ratings')) {
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
    }
    
    // 2. Create training_record_certificate_tracking table
    if (!Schema::hasTable('training_record_certificate_tracking')) {
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
    }
    
    echo "All required tables are now available!\n";
    echo "You can now run: php artisan db:seed\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}