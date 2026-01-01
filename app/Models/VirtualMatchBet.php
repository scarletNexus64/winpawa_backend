<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VirtualMatchBet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'virtual_match_id',
        'reference',
        'bet_type', // result, score, both_score, etc.
        'choice', // home_win, away_win, draw, ou score spécifique
        'amount',
        'multiplier',
        'payout',
        'is_winner',
        'status', // pending, completed, cancelled
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'payout' => 'decimal:2',
        'is_winner' => 'boolean',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bet) {
            if (empty($bet->reference)) {
                $bet->reference = 'VMB-' . strtoupper(Str::random(10));
            }
        });
    }

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function virtualMatch(): BelongsTo
    {
        return $this->belongsTo(VirtualMatch::class);
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ==================== METHODS ====================

    public static function getMultipliers(): array
    {
        return [
            'home_win' => 2.0,
            'away_win' => 2.0,
            'draw' => 3.5,
            'score_exact' => 10.0,
            'both_score' => 1.8,
        ];
    }

    public function process(string $matchResult, string $score = null): void
    {
        if ($this->status !== 'pending') {
            return;
        }

        $isWinner = $this->checkWinner($matchResult, $score);
        
        $this->is_winner = $isWinner;
        $this->payout = $isWinner ? $this->amount * $this->multiplier : 0;
        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();

        if ($isWinner) {
            $this->user->wallet->credit($this->payout, 'virtual_match_win');
        }

        // Commission d'affiliation sur les pertes
        if (!$isWinner && $this->user->referred_by) {
            $this->processAffiliateCommission();
        }
    }

    protected function checkWinner(string $matchResult, string $score = null): bool
    {
        return match ($this->bet_type) {
            'result' => $this->choice === $matchResult,
            'score' => $this->choice === $score,
            'both_score' => $this->checkBothScore($score),
            default => false,
        };
    }

    protected function checkBothScore(string $score): bool
    {
        if (!$score || $score === 'vs') {
            return false;
        }
        
        $parts = explode(' - ', $score);
        if (count($parts) !== 2) {
            return false;
        }
        
        return (int)$parts[0] > 0 && (int)$parts[1] > 0;
    }

    protected function processAffiliateCommission(): void
    {
        $referrer = $this->user->referrer;
        if (!$referrer) {
            return;
        }

        $lossCommissionRate = config('winpawa.affiliate.loss_commission', 25);
        $commission = $this->amount * ($lossCommissionRate / 100);

        $referrer->wallet->creditAffiliate($commission);

        AffiliateCommission::create([
            'referrer_id' => $referrer->id,
            'referral_id' => $this->user_id,
            'virtual_match_bet_id' => $this->id,
            'type' => 'virtual_match_loss',
            'amount' => $commission,
            'rate' => $lossCommissionRate,
        ]);
    }
}
