<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop table if it exists to ensure clean creation
        Schema::dropIfExists('training_record_certificate_tracking');
        
        // Create the table
        Schema::create('training_record_certificate_tracking', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_id', 50); // Changed to string to match Employee model
            $table->unsignedBigInteger('course_id');
            $table->date('training_date');
            $table->string('certificate_number')->nullable();
            $table->date('certificate_expiry')->nullable();
            $table->string('certificate_url')->nullable(); // Added certificate_url field
            $table->string('status')->default('Active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('employee_id');
            $table->index('course_id');
        });
        
        // Verify table was created
        if (Schema::hasTable('training_record_certificate_tracking')) {
            DB::statement("SELECT 'training_record_certificate_tracking table created successfully' as status");
        }
    }

    public function down()
    {
        Schema::dropIfExists('training_record_certificate_tracking');
    }
};
