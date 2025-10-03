<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('training_notifications')) {
            Schema::create('training_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id', 20);
                $table->text('message');
                $table->timestamp('sent_at');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                
                $table->index('employee_id');
                $table->index('is_read');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('training_notifications');
    }
};