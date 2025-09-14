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
        Schema::table('workout_template_exercises', function (Blueprint $table) {
            // Séries estimées pour chaque exercice du template
            $table->integer('estimated_sets')->default(3)->after('notes');
            $table->integer('estimated_reps')->default(10)->after('estimated_sets');
            $table->decimal('estimated_weight', 5, 2)->nullable()->after('estimated_reps');
            $table->integer('estimated_rest_time')->default(60)->after('estimated_weight'); // en secondes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_template_exercises', function (Blueprint $table) {
            $table->dropColumn(['estimated_sets', 'estimated_reps', 'estimated_weight', 'estimated_rest_time']);
        });
    }
};
