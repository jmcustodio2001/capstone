<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('training_requests') && Schema::hasColumn('training_requests', 'employee_id')) {
            Schema::table('training_requests', function (Blueprint $table) {
                // Change employee_id from integer to string to match employee table
                $table->string('employee_id', 20)->change();
            });
        }
    }

    public function down()
    {
        Schema::table('training_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->change();
        });
    }
};
