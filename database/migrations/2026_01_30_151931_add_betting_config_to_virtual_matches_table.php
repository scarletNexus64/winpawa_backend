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
            // Paramètres de timing
            $table->integer('bet_closure_seconds')->default(5)->after('starts_at');

            // Limites de mise
            $table->decimal('min_bet_amount', 15, 2)->default(100)->after('bet_closure_seconds');
            $table->decimal('max_bet_amount', 15, 2)->default(100000)->after('min_bet_amount');

            // Marchés disponibles
            $table->json('available_markets')->nullable()->after('max_bet_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_matches', function (Blueprint $table) {
            $table->dropColumn([
                'bet_closure_seconds',
                'min_bet_amount',
                'max_bet_amount',
                'available_markets',
            ]);
        });
    }
};
