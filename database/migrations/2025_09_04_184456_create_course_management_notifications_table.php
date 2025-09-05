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
        Schema::create('course_management_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competency_id');
            $table->string('competency_name');
            $table->text('message');
            $table->string('notification_type')->default('competency_notification');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['is_read', 'created_at']);
            $table->index('competency_id');
            $table->index('notification_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_management_notifications');
    }
};
