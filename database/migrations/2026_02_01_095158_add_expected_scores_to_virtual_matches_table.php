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
        Schema::table('virtual_matches', function (Blueprint $table) {
            // Scores attendus (configurés dans l'admin) - ce sont les scores finaux prévus
            $table->integer('expected_score_home')->nullable()->after('score_away');
            $table->integer('expected_score_away')->nullable()->after('expected_score_home');

            // Scores attendus pour les mi-temps
            $table->integer('expected_score_first_half_home')->nullable()->after('expected_score_away');
            $table->integer('expected_score_first_half_away')->nullable()->after('expected_score_first_half_home');
            $table->integer('expected_score_second_half_home')->nullable()->after('expected_score_first_half_away');
            $table->integer('expected_score_second_half_away')->nullable()->after('expected_score_second_half_home');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_matches', function (Blueprint $table) {
            $table->dropColumn([
                'expected_score_home',
                'expected_score_away',
                'expected_score_first_half_home',
                'expected_score_first_half_away',
                'expected_score_second_half_home',
                'expected_score_second_half_away',
            ]);
        });
    }
};
