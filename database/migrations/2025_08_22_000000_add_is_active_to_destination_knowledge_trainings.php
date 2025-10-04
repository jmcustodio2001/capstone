<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
<<<<<<< HEAD
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (!Schema::hasColumn('destination_knowledge_trainings', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('progress');
                }
            });
        }
=======
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('destination_knowledge_trainings', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('progress');
            }
        });
>>>>>>> a39bf2063dbd394f0eecd017160b7fa1336107bb
    }

    public function down()
    {
<<<<<<< HEAD
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (Schema::hasColumn('destination_knowledge_trainings', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
=======
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('destination_knowledge_trainings', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
>>>>>>> a39bf2063dbd394f0eecd017160b7fa1336107bb
    }
};
