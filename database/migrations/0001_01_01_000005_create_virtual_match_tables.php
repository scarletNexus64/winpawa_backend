<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_matches', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 15)->unique();
            $table->string('team_home');
            $table->string('team_away');
            $table->string('team_home_logo')->nullable();
            $table->string('team_away_logo')->nullable();
            $table->string('sport_type')->default('football');
            $table->integer('duration')->default(3); // minutes
            $table->string('status')->default('upcoming');
            $table->integer('score_home')->nullable();
            $table->integer('score_away')->nullable();
            $table->string('result')->nullable(); // home_win, away_win, draw
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('rng_seed', 64);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'starts_at']);
            $table->index('sport_type');
        });

        Schema::create('virtual_match_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('virtual_match_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 20)->unique();
            $table->string('bet_type'); // result, score, both_score
            $table->string('choice'); // home_win, away_win, draw, ou score spécifique
            $table->decimal('amount', 15, 2);
            $table->decimal('multiplier', 8, 2);
            $table->decimal('payout', 15, 2)->default(0);
            $table->boolean('is_winner')->default(false);
            $table->string('status')->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['virtual_match_id', 'status']);
        });

        // Ajouter la contrainte pour affiliate_commissions
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->foreign('virtual_match_bet_id')
                  ->references('id')
                  ->on('virtual_match_bets')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropForeign(['virtual_match_bet_id']);
        });
        Schema::dropIfExists('virtual_match_bets');
        Schema::dropIfExists('virtual_matches');
    }
};
