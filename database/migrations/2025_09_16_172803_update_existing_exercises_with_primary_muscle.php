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
        // Pour chaque exercice, définir le premier muscle comme principal
        DB::statement('
            UPDATE exercise_muscle_target 
            SET is_primary = true 
            WHERE id IN (
                SELECT id FROM (
                    SELECT id, ROW_NUMBER() OVER (PARTITION BY exercise_id ORDER BY id) as rn
                    FROM exercise_muscle_target
                ) as ranked
                WHERE rn = 1
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre tous les muscles comme non-principaux
        DB::statement('UPDATE exercise_muscle_target SET is_primary = false');
    }
};
