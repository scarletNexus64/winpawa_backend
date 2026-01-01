<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // roulette, scratch_card, coin_flip, etc.
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();
            $table->decimal('rtp', 5, 2)->default(75); // Return to Player %
            $table->decimal('win_frequency', 5, 2)->default(35); // Win frequency %
            $table->decimal('min_bet', 15, 2)->default(100);
            $table->decimal('max_bet', 15, 2)->default(100000);
            $table->json('multipliers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
            $table->index('type');
        });

        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 20)->unique();
            $table->decimal('amount', 15, 2);
            $table->string('choice'); // Le choix du joueur
            $table->string('result')->nullable(); // Le résultat RNG
            $table->decimal('multiplier', 8, 2)->nullable();
            $table->decimal('payout', 15, 2)->default(0);
            $table->boolean('is_winner')->default(false);
            $table->string('status')->default('pending');
            $table->string('rng_seed', 64);
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['game_id', 'status']);
            $table->index(['is_winner', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bets');
        Schema::dropIfExists('games');
    }
};
