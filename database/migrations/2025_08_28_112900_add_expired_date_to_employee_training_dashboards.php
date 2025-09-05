<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            $table->timestamp('expired_date')->nullable()->after('last_accessed');
        });
    }

    public function down()
    {
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            $table->dropColumn('expired_date');
        });
    }
};
