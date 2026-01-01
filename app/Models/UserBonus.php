<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // signup, deposit, promo
        'amount',
        'deposit_amount',
        'wagering_requirement',
        'wagered_amount',
        'status', // pending, active, completed, expired, cancelled
        'expires_at',
        'activated_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'wagering_requirement' => 'decimal:2',
        'wagered_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSignup($query)
    {
        return $query->where('type', 'signup');
    }

    // ==================== ACCESSORS ====================

    public function getRemainingWagerAttribute(): float
    {
        return max(0, $this->wagering_requirement - $this->wagered_amount);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->wagering_requirement <= 0) {
            return 100;
        }
        return min(100, ($this->wagered_amount / $this->wagering_requirement) * 100);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->wagered_amount >= $this->wagering_requirement;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // ==================== METHODS ====================

    public function activate(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'active';
        $this->activated_at = now();
        $saved = $this->save();

        if ($saved) {
            // Créditer le bonus dans le wallet
            $this->user->wallet->creditBonus($this->amount);
        }

        return $saved;
    }

    public function addWager(float $amount): void
    {
        $this->increment('wagered_amount', $amount);
        
        if ($this->is_completed) {
            $this->complete();
        }
    }

    public function complete(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->status = 'completed';
        $this->completed_at = now();
        return $this->save();
    }

    public function cancel(): bool
    {
        if (!in_array($this->status, ['pending', 'active'])) {
            return false;
        }

        // Retirer le bonus du wallet si actif
        if ($this->status === 'active') {
            $wallet = $this->user->wallet;
            $wallet->bonus_balance = max(0, $wallet->bonus_balance - $this->amount);
            $wallet->save();
        }

        $this->status = 'cancelled';
        return $this->save();
    }

    public function expire(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Retirer le bonus restant
        $wallet = $this->user->wallet;
        $wallet->bonus_balance = 0;
        $wallet->save();

        $this->status = 'expired';
        return $this->save();
    }

    public static function createSignupBonus(User $user, float $depositAmount): self
    {
        $bonusPercentage = config('winpawa.bonus.signup_percentage', 50);
        $wageringMultiplier = config('winpawa.bonus.wagering_requirement', 5);

        $bonusAmount = $depositAmount * ($bonusPercentage / 100);
        $wageringRequirement = $depositAmount * $wageringMultiplier;

        return self::create([
            'user_id' => $user->id,
            'type' => 'signup',
            'amount' => $bonusAmount,
            'deposit_amount' => $depositAmount,
            'wagering_requirement' => $wageringRequirement,
            'wagered_amount' => 0,
            'status' => 'pending',
            'expires_at' => now()->addDays(30),
        ]);
    }
}
