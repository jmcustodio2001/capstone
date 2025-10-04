<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
<<<<<<< HEAD
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (!Schema::hasColumn('destination_knowledge_trainings', 'delivery_mode')) {
                    $table->string('delivery_mode')->nullable()->after('details');
                }
                if (!Schema::hasColumn('destination_knowledge_trainings', 'expired_date')) {
                    $table->date('expired_date')->nullable()->after('delivery_mode');
                }
            });
        }
=======
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('destination_knowledge_trainings', 'delivery_mode')) {
                $table->string('delivery_mode')->nullable()->after('details');
            }
            if (!Schema::hasColumn('destination_knowledge_trainings', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('delivery_mode');
            }
        });
>>>>>>> a39bf2063dbd394f0eecd017160b7fa1336107bb
    }

    public function down(): void
    {
<<<<<<< HEAD
        if (Schema::hasTable('destination_knowledge_trainings')) {
            Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
                if (Schema::hasColumn('destination_knowledge_trainings', 'delivery_mode')) {
                    $table->dropColumn('delivery_mode');
                }
                if (Schema::hasColumn('destination_knowledge_trainings', 'expired_date')) {
                    $table->dropColumn('expired_date');
                }
            });
        }
=======
        Schema::table('destination_knowledge_trainings', function (Blueprint $table) {
            if (Schema::hasColumn('destination_knowledge_trainings', 'delivery_mode')) {
                $table->dropColumn('delivery_mode');
            }
            if (Schema::hasColumn('destination_knowledge_trainings', 'expired_date')) {
                $table->dropColumn('expired_date');
            }
        });
>>>>>>> a39bf2063dbd394f0eecd017160b7fa1336107bb
    }
};
