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
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // login_alert, security_alert, system_alert
            $table->string('title');
            $table->text('message');
            $table->json('details')->nullable();
            $table->enum('severity', ['info', 'warning', 'high', 'critical'])->default('info');
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'created_at']);
            $table->index(['severity', 'created_at']);
            $table->index(['is_read', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
