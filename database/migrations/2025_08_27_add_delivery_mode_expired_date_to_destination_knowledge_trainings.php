<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            $table->string('delivery_mode')->nullable()->after('details');
            $table->date('expired_date')->nullable()->after('delivery_mode');
        });
    }

    public function down(): void
    {
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            $table->dropColumn(['delivery_mode', 'expired_date']);
        });
    }
};
