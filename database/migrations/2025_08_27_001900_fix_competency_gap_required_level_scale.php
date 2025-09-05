<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix competency gaps that have required_level = 100 to use standard 1-5 scale
        DB::table('competency_gaps')
            ->where('required_level', 100)
            ->update([
                'required_level' => 5,
                'gap' => DB::raw('5 - current_level'),
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 100 scale if needed (though this shouldn't be used)
        DB::table('competency_gaps')
            ->where('required_level', 5)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('competency_library')
                      ->whereRaw('competency_library.id = competency_gaps.competency_id')
                      ->where('competency_name', 'LIKE', '%Destination Knowledge%');
            })
            ->update([
                'required_level' => 100,
                'gap' => DB::raw('100 - current_level'),
                'updated_at' => now()
            ]);
    }
};
