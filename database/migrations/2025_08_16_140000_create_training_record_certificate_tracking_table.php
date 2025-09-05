<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('training_record_certificate_tracking', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_id', 50); // Changed to string to match Employee model
            $table->unsignedBigInteger('course_id');
            $table->date('training_date');
            $table->string('certificate_number')->nullable();
            $table->date('certificate_expiry')->nullable();
            $table->string('status')->default('Active');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('training_record_certificate_tracking');
    }
};
