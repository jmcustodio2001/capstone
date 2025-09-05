<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_training_dashboards', 'assigned_by')) {
                $table->unsignedBigInteger('assigned_by')->nullable()->after('remarks');
            }
        });
    }

    public function down()
    {
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            if (Schema::hasColumn('employee_training_dashboards', 'assigned_by')) {
                $table->dropColumn('assigned_by');
            }
        });
    }
};
