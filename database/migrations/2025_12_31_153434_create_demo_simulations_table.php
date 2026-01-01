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
        Schema::create('demo_simulations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du scénario
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Utilisateur ciblé
            $table->enum('scenario_type', ['gain', 'perte', 'mixte'])->default('mixte'); // Type de scénario

            // Période de simulation
            $table->date('start_date');
            $table->date('end_date');

            // Données de simulation
            $table->integer('total_bets')->default(0); // Nombre total de paris
            $table->integer('bets_won')->default(0); // Paris gagnés
            $table->integer('bets_lost')->default(0); // Paris perdus
            $table->integer('games_played')->default(0); // Jeux joués
            $table->decimal('total_amount', 15, 2)->default(0); // Montant total misé
            $table->decimal('total_won', 15, 2)->default(0); // Montant gagné
            $table->decimal('total_lost', 15, 2)->default(0); // Montant perdu
            $table->decimal('net_profit', 15, 2)->default(0); // Profit net (gagné - perdu)

            // Configuration du scénario
            $table->json('scenario_config')->nullable(); // Config détaillée: distribution, variance, etc.
            $table->json('daily_data')->nullable(); // Données jour par jour pour le graphique

            // Statut
            $table->boolean('is_active')->default(false); // Si activé, remplace les vraies données
            $table->boolean('is_preview')->default(true); // Mode prévisualisation

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo_simulations');
    }
};
