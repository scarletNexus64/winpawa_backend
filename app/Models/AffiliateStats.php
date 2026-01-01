<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_referrals',
        'active_referrals',
        'total_deposits_from_referrals',
        'total_commission_earned',
        'total_commission_paid',
        'pending_commission',
    ];

    protected $casts = [
        'total_referrals' => 'integer',
        'active_referrals' => 'integer',
        'total_deposits_from_referrals' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'total_commission_paid' => 'decimal:2',
        'pending_commission' => 'decimal:2',
    ];

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== METHODS ====================

    public function incrementReferrals(): void
    {
        $this->increment('total_referrals');
        $this->increment('active_referrals');
    }

    public function addDeposit(float $amount): void
    {
        $this->increment('total_deposits_from_referrals', $amount);
    }

    public function addCommission(float $amount): void
    {
        $this->increment('total_commission_earned', $amount);
        $this->increment('pending_commission', $amount);
    }

    public function payCommission(float $amount): void
    {
        $this->increment('total_commission_paid', $amount);
        $this->decrement('pending_commission', min($amount, $this->pending_commission));
    }

    public function recalculate(): void
    {
        $user = $this->user;

        $this->total_referrals = $user->referrals()->count();
        $this->active_referrals = $user->referrals()
            ->where('is_active', true)
            ->whereHas('bets')
            ->count();

        $this->total_deposits_from_referrals = Transaction::whereIn(
            'user_id',
            $user->referrals()->pluck('id')
        )->where('type', 'deposit')
         ->where('status', 'completed')
         ->sum('amount');

        $commissions = AffiliateCommission::where('referrer_id', $user->id);
        
        $this->total_commission_earned = $commissions->sum('amount');
        $this->total_commission_paid = $commissions->where('is_paid', true)->sum('amount');
        $this->pending_commission = $this->total_commission_earned - $this->total_commission_paid;

        $this->save();
    }
}
