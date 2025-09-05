<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('training_record_certificate_tracking', function (Blueprint $table) {
            $table->string('certificate_url')->nullable()->after('certificate_expiry');
        });
    }
    public function down() {
        Schema::table('training_record_certificate_tracking', function (Blueprint $table) {
            $table->dropColumn('certificate_url');
        });
    }
};
