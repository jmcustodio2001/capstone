<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_training_dashboards', 'source')) {
                $table->string('source')->nullable()->after('assigned_by');
            }
        });
    }

    public function down(): void {
        Schema::table('employee_training_dashboards', function (Blueprint $table) {
            if (Schema::hasColumn('employee_training_dashboards', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
