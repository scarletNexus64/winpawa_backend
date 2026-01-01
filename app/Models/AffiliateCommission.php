<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referral_id',
        'bet_id',
        'virtual_match_bet_id',
        'transaction_id',
        'type', // deposit, loss, virtual_match_loss
        'amount',
        'rate',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    // ==================== RELATIONS ====================

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referral_id');
    }

    public function bet(): BelongsTo
    {
        return $this->belongsTo(Bet::class);
    }

    public function virtualMatchBet(): BelongsTo
    {
        return $this->belongsTo(VirtualMatchBet::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // ==================== SCOPES ====================

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeForReferrer($query, int $referrerId)
    {
        return $query->where('referrer_id', $referrerId);
    }

    // ==================== METHODS ====================

    public function markAsPaid(): bool
    {
        $this->is_paid = true;
        $this->paid_at = now();
        return $this->save();
    }
}
