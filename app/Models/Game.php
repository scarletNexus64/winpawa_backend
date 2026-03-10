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
        'image',
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

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        // Automatically regenerate roulette prizes when multipliers or win_frequency change
        static::saving(function ($game) {
            if ($game->type === GameType::ROULETTE && ($game->isDirty('multipliers') || $game->isDirty('win_frequency'))) {
                $multipliers = $game->multipliers ?? [2, 5, 10];
                $winFrequency = $game->win_frequency ?? 50;
                $currentSettings = $game->settings ?? [];

                // Générer les prizes avec segments gagnants + perdants basés sur win_frequency
                $currentSettings['prizes'] = self::generateRoulettePrizesWithLosses($multipliers, $winFrequency);
                $currentSettings['segments'] = count($currentSettings['prizes']);
                $currentSettings['winning_segments'] = count($multipliers); // Nombre de segments gagnants
                $currentSettings['win_frequency'] = $winFrequency;

                $game->settings = $currentSettings;
            }
        });
    }

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

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Toutes les images sont maintenant dans public/images
        return asset('images/' . $this->image);
    }

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

    /**
     * Generate roulette prizes from multipliers array (WITHOUT losses - legacy)
     */
    public static function generateRoulettePrizes(array $multipliers): array
    {
        $colors = ['#FF6B6B', '#4ECDC4', '#FFD93D', '#95E1D3', '#F38181', '#AA96DA', '#FCBAD3', '#FFA07A'];
        $prizes = [];

        foreach ($multipliers as $index => $multiplier) {
            $segmentNumber = $index + 1;
            $prizes[$segmentNumber] = [
                'multiplier' => $multiplier,
                'color' => $colors[$index % count($colors)],
            ];
        }

        return $prizes;
    }

    /**
     * Generate roulette prizes WITH losing segments based on win_frequency
     * Segments are INTELLIGENTLY DISTRIBUTED to avoid clustering
     *
     * Example:
     * - Multipliers: [2, 5, 10]
     * - Win frequency: 50%
     * - Result: 6 segments with winners evenly distributed
     *   Segment 1: 2x (winning)
     *   Segment 2: 0x (losing)
     *   Segment 3: 5x (winning)
     *   Segment 4: 0x (losing)
     *   Segment 5: 10x (winning)
     *   Segment 6: 0x (losing)
     */
    public static function generateRoulettePrizesWithLosses(array $multipliers, float $winFrequency): array
    {
        $winningColors = ['#FF6B6B', '#4ECDC4', '#FFD93D', '#95E1D3', '#F38181', '#AA96DA'];
        $losingColors = ['#2C3E50', '#34495E', '#1C2833', '#17202A', '#212F3C', '#273746'];

        $winningCount = count($multipliers);

        // Calculer le nombre de segments perdants nécessaires pour respecter win_frequency
        // IMPROVED: Utiliser ceil() pour s'assurer d'atteindre au minimum le win_frequency
        // Si win_frequency = 35% et winningCount = 3:
        // totalSegments = ceil(3 / 0.35) = ceil(8.57) = 9
        // Mais 3/9 = 33.33% < 35%, donc on utilise floor() pour favoriser le joueur
        $totalSegments = $winFrequency > 0 ? floor($winningCount / ($winFrequency / 100)) : $winningCount * 2;

        // Si floor donne 0 ou moins que winningCount, utiliser au moins winningCount
        if ($totalSegments < $winningCount) {
            $totalSegments = $winningCount;
        }

        $losingCount = max(0, $totalSegments - $winningCount);

        // Créer les segments gagnants
        $winningSegments = [];
        foreach ($multipliers as $index => $multiplier) {
            $winningSegments[] = [
                'multiplier' => $multiplier,
                'color' => $winningColors[$index % count($winningColors)],
                'is_winner' => true,
            ];
        }

        // Créer les segments perdants
        $losingSegments = [];
        for ($i = 0; $i < $losingCount; $i++) {
            $losingSegments[] = [
                'multiplier' => 0,
                'color' => $losingColors[$i % count($losingColors)],
                'is_winner' => false,
            ];
        }

        // ALGORITHME INTELLIGENT : Distribuer les segments pour éviter le clustering
        $prizes = [];
        $segmentNumber = 1;

        // Calculer l'espacement idéal entre les segments gagnants
        $spacing = $losingCount > 0 ? floor($losingCount / $winningCount) : 0;
        $remainingLosers = $losingCount;

        $winIndex = 0;
        $loseIndex = 0;

        while ($winIndex < $winningCount || $loseIndex < $losingCount) {
            // Ajouter un segment gagnant si disponible
            if ($winIndex < $winningCount) {
                $prizes[$segmentNumber] = $winningSegments[$winIndex];
                $segmentNumber++;
                $winIndex++;

                // Ajouter des segments perdants après chaque gagnant (distribution équitable)
                $losersToAdd = $remainingLosers > 0 && $winningCount > 0
                    ? min($spacing + ($remainingLosers % ($winningCount - $winIndex + 1) > 0 ? 1 : 0), $remainingLosers)
                    : 0;

                for ($i = 0; $i < $losersToAdd && $loseIndex < $losingCount; $i++) {
                    $prizes[$segmentNumber] = $losingSegments[$loseIndex];
                    $segmentNumber++;
                    $loseIndex++;
                    $remainingLosers--;
                }
            }
        }

        // Ajouter les segments perdants restants (si nécessaire)
        while ($loseIndex < $losingCount) {
            $prizes[$segmentNumber] = $losingSegments[$loseIndex];
            $segmentNumber++;
            $loseIndex++;
        }

        return $prizes;
    }

    public static function getDefaultSettings(GameType $type): array
    {
        return match ($type) {
            GameType::ROULETTE => [
                'segments' => 8,
                'prizes' => self::generateRoulettePrizes([2, 5, 10, 20, 2, 5, 10, 20]),
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
