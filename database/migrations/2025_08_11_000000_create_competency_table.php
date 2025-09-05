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
        Schema::create('competency_library', function (Blueprint $table) {
            $table->id();
            $table->string('competency_name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->unsignedTinyInteger('rate')->nullable(); // Added rate column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_library');
    }
};
