<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('training_notifications')) {
            Schema::create('training_notifications', function (Blueprint $table) {
                $table->id('notification_id');
                $table->unsignedBigInteger('employee_id');
                $table->text('message');
                $table->dateTime('sent_at');
                $table->timestamps();
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('training_notifications');
    }
};
