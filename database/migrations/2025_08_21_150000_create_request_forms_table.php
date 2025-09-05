<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_forms', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('employee_id');
            $table->string('request_type');
            $table->text('reason');
            $table->string('status')->default('Pending');
            $table->date('requested_date');
            // $table->timestamps(); // Not used in Blade, so omitted
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_forms');
    }
};
