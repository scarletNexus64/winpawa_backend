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
        Schema::create('demo_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->json('selected_games')->nullable(); // Array de game IDs
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('win_rate', 5, 2)->default(45.00); // Pourcentage de victoires (ex: 45.00%)
            $table->decimal('min_bet', 10, 2)->default(100.00);
            $table->decimal('max_bet', 10, 2)->default(10000.00);
            $table->decimal('min_win_multiplier', 5, 2)->default(1.50);
            $table->decimal('max_win_multiplier', 5, 2)->default(10.00);
            $table->integer('daily_bet_count')->default(10); // Nombre de paris par jour
            $table->json('configuration')->nullable(); // Configs additionnelles
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo_configurations');
    }
};
