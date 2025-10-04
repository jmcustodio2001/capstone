<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

try {
    echo "Creating training_record_certificate_tracking table...\n";
    
    if (Schema::hasTable('training_record_certificate_tracking')) {
        Schema::drop('training_record_certificate_tracking');
        echo "Dropped existing table\n";
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
    
    echo "Table created successfully!\n";
    
    // Verify table exists
    if (Schema::hasTable('training_record_certificate_tracking')) {
        echo "Table verification: SUCCESS\n";
    } else {
        echo "Table verification: FAILED\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}