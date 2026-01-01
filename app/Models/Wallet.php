<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'main_balance',
        'bonus_balance',
        'affiliate_balance',
        'currency',
        'is_locked',
    ];

    protected $casts = [
        'main_balance' => 'decimal:2',
        'bonus_balance' => 'decimal:2',
        'affiliate_balance' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ==================== ACCESSORS ====================

    public function getTotalBalanceAttribute(): float
    {
        return $this->main_balance + $this->bonus_balance;
    }

    public function getPlayableBalanceAttribute(): float
    {
        return $this->main_balance + $this->bonus_balance;
    }

    public function getWithdrawableBalanceAttribute(): float
    {
        // Seul le solde principal est retirable
        return $this->main_balance;
    }

    // ==================== METHODS ====================

    public function canDebit(float $amount): bool
    {
        return !$this->is_locked && $this->total_balance >= $amount;
    }

    public function debit(float $amount, string $type = 'bet'): bool
    {
        if (!$this->canDebit($amount)) {
            return false;
        }

        $remaining = $amount;

        // D'abord utiliser le bonus, puis le solde principal
        if ($this->bonus_balance > 0) {
            $bonusDebit = min($this->bonus_balance, $remaining);
            $this->bonus_balance -= $bonusDebit;
            $remaining -= $bonusDebit;
        }

        if ($remaining > 0) {
            $this->main_balance -= $remaining;
        }

        return $this->save();
    }

    public function credit(float $amount, string $type = 'win'): bool
    {
        // Les gains vont toujours dans le solde principal
        $this->main_balance += $amount;
        return $this->save();
    }

    public function creditBonus(float $amount): bool
    {
        $this->bonus_balance += $amount;
        return $this->save();
    }

    public function creditAffiliate(float $amount): bool
    {
        $this->affiliate_balance += $amount;
        return $this->save();
    }

    public function transferAffiliateToMain(): bool
    {
        if ($this->affiliate_balance <= 0) {
            return false;
        }

        $this->main_balance += $this->affiliate_balance;
        $this->affiliate_balance = 0;

        return $this->save();
    }

    public function lock(): bool
    {
        $this->is_locked = true;
        return $this->save();
    }

    public function unlock(): bool
    {
        $this->is_locked = false;
        return $this->save();
    }
}
