<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            $table->string('source')->nullable()->after('status');
            $table->string('assigned_by')->nullable()->after('source');
            $table->timestamp('assigned_date')->nullable()->after('assigned_by');
            $table->unsignedBigInteger('destination_training_id')->nullable()->after('assigned_date');
            $table->boolean('needs_response')->default(false)->after('destination_training_id');
        });
    }

    public function down(): void
    {
        Schema::table('upcoming_trainings', function (Blueprint $table) {
            $table->dropColumn(['source', 'assigned_by', 'assigned_date', 'destination_training_id', 'needs_response']);
        });
    }
};
