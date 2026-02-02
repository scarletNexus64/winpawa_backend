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
            // Événements du match (buts, cartons, etc.)
            $table->json('match_events')->nullable()->after('metadata');

            // Scores par quart-temps (basketball)
            $table->json('quarter_scores')->nullable()->after('match_events');

            // Scores par set (tennis)
            $table->json('set_scores')->nullable()->after('quarter_scores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_matches', function (Blueprint $table) {
            $table->dropColumn(['match_events', 'quarter_scores', 'set_scores']);
        });
    }
};
