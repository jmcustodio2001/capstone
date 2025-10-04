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
<<<<<<< HEAD
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (!Schema::hasColumn('destination_knowledge_trainings', 'status')) {
                    $table->string('status')->default('not-started')->after('progress');
                }
            });
        }
=======
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('destination_knowledge_trainings', 'status')) {
                $table->string('status')->default('Not Started')->after('progress');
            }
        });
>>>>>>> a39bf2063dbd394f0eecd017160b7fa1336107bb
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<< HEAD
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (Schema::hasColumn('destination_knowledge_trainings', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
=======
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('destination_knowledge_trainings', 'status')) {
                $table->dropColumn('status');
            }
        });
>>>>>>> a39bf2063dbd394f0eecd017160b7fa1336107bb
    }
};
