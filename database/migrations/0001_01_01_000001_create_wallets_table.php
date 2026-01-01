<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('main_balance', 15, 2)->default(0);
            $table->decimal('bonus_balance', 15, 2)->default(0);
            $table->decimal('affiliate_balance', 15, 2)->default(0);
            $table->string('currency', 5)->default('XAF');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index('is_locked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
