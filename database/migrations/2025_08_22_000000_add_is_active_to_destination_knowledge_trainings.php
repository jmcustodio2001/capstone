<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (!Schema::hasColumn('destination_knowledge_trainings', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('progress');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (Schema::hasColumn('destination_knowledge_trainings', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
