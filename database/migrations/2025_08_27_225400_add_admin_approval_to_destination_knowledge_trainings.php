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
                if (!Schema::hasColumn('destination_knowledge_trainings', 'admin_approved_for_upcoming')) {
                    $table->boolean('admin_approved_for_upcoming')->default(false)->after('is_active');
                }
            });
        }
=======
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('destination_knowledge_trainings', 'admin_approved_for_upcoming')) {
                $table->boolean('admin_approved_for_upcoming')->default(false)->after('is_active');
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
                if (Schema::hasColumn('destination_knowledge_trainings', 'admin_approved_for_upcoming')) {
                    $table->dropColumn('admin_approved_for_upcoming');
                }
            });
        }
=======
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('destination_knowledge_trainings', 'admin_approved_for_upcoming')) {
                $table->dropColumn('admin_approved_for_upcoming');
            }
        });
>>>>>>> a39bf2063dbd394f0eecd017160b7fa1336107bb
    }
};
