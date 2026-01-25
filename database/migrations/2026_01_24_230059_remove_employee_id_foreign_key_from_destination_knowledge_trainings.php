<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            // Drop the foreign key constraint to allow external employees from API
            $table->dropForeign(['employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }
};
