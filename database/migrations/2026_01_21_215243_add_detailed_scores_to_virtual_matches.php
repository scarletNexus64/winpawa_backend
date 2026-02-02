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
            // Scores détaillés
            $table->integer('score_first_half_home')->nullable()->after('score_away');
            $table->integer('score_first_half_away')->nullable()->after('score_first_half_home');
            $table->integer('score_second_half_home')->nullable()->after('score_first_half_away');
            $table->integer('score_second_half_away')->nullable()->after('score_second_half_home');

            // Prolongation et penalties
            $table->boolean('has_extra_time')->default(false)->after('score_second_half_away');
            $table->boolean('has_penalties')->default(false)->after('has_extra_time');
            $table->integer('score_extra_time_home')->nullable()->after('has_penalties');
            $table->integer('score_extra_time_away')->nullable()->after('score_extra_time_home');
            $table->integer('score_penalties_home')->nullable()->after('score_extra_time_away');
            $table->integer('score_penalties_away')->nullable()->after('score_penalties_home');

            // Informations de la ligue
            $table->string('league')->nullable()->after('sport_type');
            $table->string('season')->nullable()->after('league');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_matches', function (Blueprint $table) {
            $table->dropColumn([
                'score_first_half_home',
                'score_first_half_away',
                'score_second_half_home',
                'score_second_half_away',
                'has_extra_time',
                'has_penalties',
                'score_extra_time_home',
                'score_extra_time_away',
                'score_penalties_home',
                'score_penalties_away',
                'league',
                'season',
            ]);
        });
    }
};
