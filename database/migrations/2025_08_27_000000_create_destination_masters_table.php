<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('destination_masters', function (Blueprint $table) {
            $table->id();
            $table->string('destination_name');
            $table->text('details');
            $table->text('objectives');
            $table->string('duration');
            $table->string('delivery_mode');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_masters');
    }
};
