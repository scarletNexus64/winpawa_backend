<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('total_referrals')->default(0);
            $table->integer('active_referrals')->default(0);
            $table->decimal('total_deposits_from_referrals', 15, 2)->default(0);
            $table->decimal('total_commission_earned', 15, 2)->default(0);
            $table->decimal('total_commission_paid', 15, 2)->default(0);
            $table->decimal('pending_commission', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique('user_id');
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referral_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('virtual_match_bet_id')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // deposit, loss, virtual_match_loss
            $table->decimal('amount', 15, 2);
            $table->decimal('rate', 5, 2);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['referrer_id', 'is_paid']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('user_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // signup, deposit, promo
            $table->decimal('amount', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->decimal('wagering_requirement', 15, 2);
            $table->decimal('wagered_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, active, completed, expired, cancelled
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bonuses');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_stats');
    }
};
