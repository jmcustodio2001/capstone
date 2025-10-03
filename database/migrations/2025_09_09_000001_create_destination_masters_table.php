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
        // Check if table already exists before creating
        if (!Schema::hasTable('destination_masters')) {
            Schema::create('destination_masters', function (Blueprint $table) {
            $table->id();
            $table->string('destination_name')->unique();
            $table->text('details');
            $table->text('objectives');
            $table->string('duration', 100);
            $table->string('delivery_mode', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for better performance
            $table->index('destination_name');
            $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_masters');
    }
};
