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
        // Skip foreign key creation - will be handled at application level
        // Foreign key constraints can cause issues with string primary keys
        // The relationships are defined in the Eloquent models instead
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No foreign keys to drop since none were created
    }
};
