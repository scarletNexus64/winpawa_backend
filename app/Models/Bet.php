<?php

namespace App\Models;

use App\Enums\BetStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'reference',
        'amount',
        'choice', // Le choix du joueur (ex: "heads", "red", "3")
        'result', // Le résultat du RNG
        'multiplier',
        'payout',
        'is_winner',
        'status',
        'rng_seed', // Pour audit/vérification
        'metadata', // Données supplémentaires
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'payout' => 'decimal:2',
        'is_winner' => 'boolean',
        'status' => BetStatus::class,
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bet) {
            if (empty($bet->reference)) {
                $bet->reference = self::generateReference();
            }
            if (empty($bet->rng_seed)) {
                $bet->rng_seed = self::generateRngSeed();
            }
        });
    }

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', BetStatus::PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', BetStatus::COMPLETED);
    }

    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    public function scopeLosers($query)
    {
        return $query->where('is_winner', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByGame($query, int $gameId)
    {
        return $query->where('game_id', $gameId);
    }

    // ==================== ACCESSORS ====================

    public function getNetResultAttribute(): float
    {
        return $this->is_winner ? ($this->payout - $this->amount) : -$this->amount;
    }

    public function getFormattedResultAttribute(): string
    {
        return $this->is_winner 
            ? '+' . number_format($this->payout, 0) . ' FCFA'
            : '-' . number_format($this->amount, 0) . ' FCFA';
    }

    // ==================== METHODS ====================

    public static function generateReference(): string
    {
        return 'BET-' . strtoupper(Str::random(10));
    }

    public static function generateRngSeed(): string
    {
        return hash('sha256', uniqid(mt_rand(), true) . microtime(true));
    }

    public function process(): void
    {
        if ($this->status !== BetStatus::PENDING) {
            return;
        }

        $game = $this->game;
        
        // Générer le résultat RNG
        $isWinner = $this->determineWinner();
        
        $this->is_winner = $isWinner;
        $this->multiplier = $isWinner ? $game->getRandomMultiplier() : 0;
        $this->payout = $isWinner ? $this->amount * $this->multiplier : 0;
        $this->status = BetStatus::COMPLETED;
        $this->processed_at = now();
        
        $this->save();

        // Créditer le wallet si gagnant
        if ($isWinner) {
            $this->user->wallet->credit($this->payout, 'win');
        }

        // Calculer les commissions d'affiliation
        if (!$isWinner && $this->user->referred_by) {
            $this->processAffiliateCommission();
        }
    }

    protected function determineWinner(): bool
    {
        $game = $this->game;
        
        // Utiliser le seed RNG pour la reproductibilité
        $hash = hash('sha256', $this->rng_seed . $this->id);
        $randomValue = hexdec(substr($hash, 0, 8)) / 0xFFFFFFFF * 100;
        
        return $randomValue <= $game->win_frequency;
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

        // Enregistrer la transaction d'affiliation
        AffiliateCommission::create([
            'referrer_id' => $referrer->id,
            'referral_id' => $this->user_id,
            'bet_id' => $this->id,
            'type' => 'loss',
            'amount' => $commission,
            'rate' => $lossCommissionRate,
        ]);
    }
}
