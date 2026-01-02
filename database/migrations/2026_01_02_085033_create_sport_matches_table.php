<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $table->string('home_team');
            $table->string('away_team');
            $table->string('home_logo')->nullable();
            $table->string('away_logo')->nullable();
            $table->string('league')->nullable();
            $table->timestamp('match_time');
            $table->string('status')->default('upcoming'); // upcoming, live, finished
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->json('odds')->nullable(); // cotes pour paris
            $table->json('statistics')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_matches');
    }
};
