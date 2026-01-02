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
        Schema::create('demo_simulated_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demo_configuration_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->decimal('total_bet_amount', 12, 2)->default(0);
            $table->decimal('total_win_amount', 12, 2)->default(0);
            $table->decimal('total_loss_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0); // win - loss
            $table->integer('bet_count')->default(0);
            $table->integer('win_count')->default(0);
            $table->integer('loss_count')->default(0);
            $table->decimal('win_rate_actual', 5, 2)->default(0); // Taux de victoire réel
            $table->json('hourly_data')->nullable(); // Données par heure pour les graphiques
            $table->json('game_breakdown')->nullable(); // Répartition par jeu
            $table->json('metadata')->nullable(); // Métadonnées additionnelles
            $table->timestamps();

            // Index pour améliorer les performances des requêtes
            $table->index(['demo_configuration_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index('period_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo_simulated_data');
    }
};
