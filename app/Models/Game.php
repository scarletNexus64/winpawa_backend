<?php

namespace App\Models;

use App\Enums\GameStatus;
use App\Enums\GameType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'category_id',
        'name',
        'slug',
        'type',
        'description',
        'thumbnail',
        'banner',
        'rtp', // Return to Player (%)
        'win_frequency', // Fréquence de gains (%)
        'min_bet',
        'max_bet',
        'multipliers', // JSON: ex. [2, 5, 10]
        'is_active',
        'is_featured',
        'is_configured',
        'is_maintenance',
        'maintenance_message',
        'sort_order',
        'settings', // JSON pour config spécifique au jeu
    ];

    protected $casts = [
        'type' => GameType::class,
        'rtp' => 'decimal:2',
        'win_frequency' => 'decimal:2',
        'min_bet' => 'decimal:2',
        'max_bet' => 'decimal:2',
        'multipliers' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_configured' => 'boolean',
        'is_maintenance' => 'boolean',
    ];

    // ==================== RELATIONS ====================

    public function module(): BelongsTo
    {
        return $this->belongsTo(GameModule::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GameCategory::class);
    }

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // ==================== ACCESSORS ====================

    public function getHouseEdgeAttribute(): float
    {
        return 100 - $this->rtp;
    }

    public function getFormattedRtpAttribute(): string
    {
        return number_format($this->rtp, 1) . '%';
    }

    public function getTotalBetsAttribute(): int
    {
        return $this->bets()->count();
    }

    public function getTotalWageredAttribute(): float
    {
        return $this->bets()->sum('amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->bets()->where('is_winner', true)->sum('payout');
    }

    public function getActualRtpAttribute(): float
    {
        $wagered = $this->total_wagered;
        if ($wagered <= 0) {
            return 0;
        }
        return ($this->total_paid / $wagered) * 100;
    }

    // ==================== METHODS ====================

    public function getRandomMultiplier(): float
    {
        if (empty($this->multipliers)) {
            return 2.0;
        }
        return $this->multipliers[array_rand($this->multipliers)];
    }

    public function shouldWin(): bool
    {
        // Basé sur la fréquence de gains configurée
        $random = mt_rand(1, 10000) / 100;
        return $random <= $this->win_frequency;
    }

    public function calculatePayout(float $betAmount, float $multiplier = null): float
    {
        $multiplier = $multiplier ?? $this->getRandomMultiplier();
        return $betAmount * $multiplier;
    }

    public static function getDefaultSettings(GameType $type): array
    {
        return match ($type) {
            GameType::ROULETTE => [
                'segments' => 8,
                'colors' => ['red', 'black', 'green'],
            ],
            GameType::SCRATCH_CARD => [
                'cards_count' => 9,
                'winning_cards' => 3,
            ],
            GameType::COIN_FLIP => [
                'options' => ['heads', 'tails'],
            ],
            GameType::DICE => [
                'sides' => 6,
                'options' => ['odd', 'even', 'specific'],
            ],
            GameType::ROCK_PAPER_SCISSORS => [
                'options' => ['rock', 'paper', 'scissors'],
            ],
            GameType::TREASURE_BOX => [
                'boxes_count' => 3,
            ],
            GameType::LUCKY_NUMBER => [
                'range_min' => 1,
                'range_max' => 10,
            ],
            GameType::JACKPOT => [
                'segments' => 6,
            ],
            GameType::PENALTY => [
                'positions' => 5,
            ],
            GameType::LUDO => [
                'players' => 4,
            ],
            GameType::QUIZ => [
                'options_count' => 4,
            ],
            GameType::COLOR_ROULETTE => [
                'colors' => ['red', 'blue', 'green', 'yellow'],
            ],
            default => [],
        };
    }
}
