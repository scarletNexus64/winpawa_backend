<?php

namespace App\Models;

use App\Enums\VirtualMatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VirtualMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'team_home',
        'team_away',
        'team_home_logo',
        'team_away_logo',
        'sport_type',
        'league',
        'season',
        'duration', // en minutes (1, 3, 5)
        'status',
        'score_home', // Score actuel en temps réel
        'score_away', // Score actuel en temps réel
        'expected_score_home', // Score final attendu (configuré)
        'expected_score_away', // Score final attendu (configuré)
        'score_first_half_home',
        'score_first_half_away',
        'score_second_half_home',
        'score_second_half_away',
        'expected_score_first_half_home', // Score mi-temps attendu
        'expected_score_first_half_away', // Score mi-temps attendu
        'expected_score_second_half_home', // Score 2e mi-temps attendu
        'expected_score_second_half_away', // Score 2e mi-temps attendu
        'has_extra_time',
        'has_penalties',
        'score_extra_time_home',
        'score_extra_time_away',
        'score_penalties_home',
        'score_penalties_away',
        'result', // home_win, away_win, draw
        'starts_at',
        'ends_at',
        'bet_closure_seconds',
        'min_bet_amount',
        'max_bet_amount',
        'available_markets',
        'odds', // Cotes personnalisées par type de pari
        'rng_seed',
        'metadata',
        'match_events',
        'quarter_scores',
        'set_scores',
    ];

    protected $casts = [
        'duration' => 'integer',
        'status' => VirtualMatchStatus::class,
        'score_home' => 'integer',
        'score_away' => 'integer',
        'expected_score_home' => 'integer',
        'expected_score_away' => 'integer',
        'score_first_half_home' => 'integer',
        'score_first_half_away' => 'integer',
        'score_second_half_home' => 'integer',
        'score_second_half_away' => 'integer',
        'expected_score_first_half_home' => 'integer',
        'expected_score_first_half_away' => 'integer',
        'expected_score_second_half_home' => 'integer',
        'expected_score_second_half_away' => 'integer',
        'has_extra_time' => 'boolean',
        'has_penalties' => 'boolean',
        'score_extra_time_home' => 'integer',
        'score_extra_time_away' => 'integer',
        'score_penalties_home' => 'integer',
        'score_penalties_away' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'bet_closure_seconds' => 'integer',
        'min_bet_amount' => 'decimal:2',
        'max_bet_amount' => 'decimal:2',
        'available_markets' => 'array',
        'odds' => 'array',
        'metadata' => 'array',
        'match_events' => 'array',
        'quarter_scores' => 'array',
        'set_scores' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($match) {
            if (empty($match->reference)) {
                $match->reference = self::generateReference();
            }
            if (empty($match->rng_seed)) {
                $match->rng_seed = hash('sha256', uniqid(mt_rand(), true));
            }

            // Si le match est en direct, forcer la date de début à maintenant
            if ($match->status === VirtualMatchStatus::LIVE) {
                $match->starts_at = now();
            }
        });

        static::updating(function ($match) {
            // Si on change le statut vers "live", forcer la date de début à maintenant
            if ($match->isDirty('status') && $match->status === VirtualMatchStatus::LIVE) {
                $match->starts_at = now();
            }
        });

        // Convertir les cotes du format Repeater au format key-value
        static::saving(function ($match) {
            if ($match->odds && is_array($match->odds)) {
                $match->odds = self::normalizeOdds($match->odds);
            }
        });
    }

    /**
     * Normaliser les cotes du format Repeater au format key-value
     */
    protected static function normalizeOdds(array $odds): array
    {
        $normalized = [];

        foreach ($odds as $market => $options) {
            if (!is_array($options)) continue;

            $normalized[$market] = [];

            foreach ($options as $option) {
                if (isset($option['key'])) {
                    $normalized[$market][$option['key']] = [
                        'label' => $option['label'] ?? '',
                        'description' => $option['description'] ?? '',
                        'odd' => (float) ($option['odd'] ?? 1.0),
                    ];
                }
            }
        }

        return $normalized;
    }

    // ==================== RELATIONS ====================

    public function bets(): HasMany
    {
        return $this->hasMany(VirtualMatchBet::class);
    }

    // ==================== SCOPES ====================

    public function scopeUpcoming($query)
    {
        return $query->where('status', VirtualMatchStatus::UPCOMING)
                     ->where('starts_at', '>', now());
    }

    public function scopeLive($query)
    {
        return $query->where('status', VirtualMatchStatus::LIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', VirtualMatchStatus::COMPLETED);
    }

    public function scopeBettable($query)
    {
        return $query->where('status', VirtualMatchStatus::UPCOMING)
                     ->where('starts_at', '>', now()->addSeconds(30));
    }

    // ==================== ACCESSORS ====================

    public function getScoreAttribute(): string
    {
        if ($this->status === VirtualMatchStatus::UPCOMING) {
            return 'vs';
        }
        return "{$this->score_home} - {$this->score_away}";
    }

    public function getCountdownAttribute(): int
    {
        if (!$this->starts_at) {
            return 0;
        }
        return max(0, now()->diffInSeconds($this->starts_at, false));
    }

    public function getIsOpenForBetsAttribute(): bool
    {
        $closureSeconds = $this->bet_closure_seconds ?? 5;

        return $this->status === VirtualMatchStatus::UPCOMING
               && $this->starts_at > now()->addSeconds($closureSeconds);
    }

    // ==================== METHODS ====================

    public static function generateReference(): string
    {
        return 'VM-' . strtoupper(Str::random(8));
    }

    public function start(): void
    {
        // TOUJOURS initialiser les scores à 0-0 au démarrage
        // Les scores expected_score_home/away contiennent les scores finaux attendus
        $this->score_home = 0;
        $this->score_away = 0;

        // Réinitialiser aussi les scores de mi-temps
        $this->score_first_half_home = 0;
        $this->score_first_half_away = 0;
        $this->score_second_half_home = 0;
        $this->score_second_half_away = 0;

        $this->status = VirtualMatchStatus::LIVE;
        $this->save();

        // Broadcaster l'événement de démarrage du match
        event(new \App\Events\VirtualMatchStarted($this));
    }

    public function updateScore(int $homeScore, int $awayScore, ?array $matchEvent = null): void
    {
        $this->score_home = $homeScore;
        $this->score_away = $awayScore;

        // Ajouter l'événement au tableau des événements du match
        if ($matchEvent) {
            $events = $this->match_events ?? [];
            $events[] = $matchEvent;
            $this->match_events = $events;
        }

        $this->save();

        // Broadcaster la mise à jour du match
        event(new \App\Events\VirtualMatchUpdated($this, $matchEvent));
    }

    public function complete(): void
    {
        $this->generateResult();
        $this->status = VirtualMatchStatus::COMPLETED;
        $this->ends_at = now();
        $this->save();

        // Broadcaster l'événement de fin du match
        event(new \App\Events\VirtualMatchCompleted($this));

        // Traiter tous les paris
        $this->processBets();
    }

    protected function generateResult(): void
    {
        // Les scores sont déjà à jour grâce aux événements simulés
        // On doit juste déterminer le résultat basé sur les scores finaux

        // Si pour une raison quelconque les scores sont toujours à 0-0 et qu'on a des expected_scores
        // On utilise les expected_scores comme fallback
        if ($this->score_home === 0 && $this->score_away === 0 &&
            ($this->expected_score_home !== null || $this->expected_score_away !== null)) {
            $this->score_home = $this->expected_score_home ?? 0;
            $this->score_away = $this->expected_score_away ?? 0;
        }

        // Déterminer le résultat basé sur les scores finaux
        if ($this->score_home > $this->score_away) {
            $this->result = 'home_win';
        } elseif ($this->score_away > $this->score_home) {
            $this->result = 'away_win';
        } else {
            $this->result = 'draw';
        }
    }

    protected function processBets(): void
    {
        foreach ($this->bets()->where('status', 'pending')->get() as $bet) {
            $bet->process($this->result, $this->score);
        }
    }

    public static function getTeamNames(): array
    {
        return [
            'Lions FC', 'Eagles United', 'Thunder SC', 'Storm City',
            'Phoenix Rising', 'Dragons FC', 'Warriors United', 'Titans SC',
            'Falcons FC', 'Panthers City', 'Bulls FC', 'Hawks United',
            'Wolves SC', 'Bears FC', 'Tigers United', 'Sharks City',
        ];
    }

    public static function createUpcoming(int $minutesFromNow = 5, int $duration = 3): self
    {
        $teams = collect(self::getTeamNames())->shuffle()->take(2);

        return self::create([
            'team_home' => $teams[0],
            'team_away' => $teams[1],
            'sport_type' => 'football',
            'duration' => $duration,
            'status' => VirtualMatchStatus::UPCOMING,
            'starts_at' => now()->addMinutes($minutesFromNow),
        ]);
    }

    /**
     * Obtenir les cotes par défaut pour tous les marchés
     */
    public static function getDefaultOdds(): array
    {
        return [
            'result' => [
                'home_win' => ['label' => 'Victoire domicile (1)', 'description' => 'L\'équipe à domicile gagne le match', 'odd' => 2.0],
                'draw' => ['label' => 'Match nul (X)', 'description' => 'Les deux équipes font match nul', 'odd' => 3.5],
                'away_win' => ['label' => 'Victoire extérieur (2)', 'description' => 'L\'équipe à l\'extérieur gagne le match', 'odd' => 2.0],
            ],
            'both_teams_score' => [
                'yes' => ['label' => 'Oui', 'description' => 'Les deux équipes marquent au moins 1 but', 'odd' => 1.8],
                'no' => ['label' => 'Non', 'description' => 'Au moins une équipe ne marque pas', 'odd' => 2.1],
            ],
            'over_under' => [
                'over_1_5' => ['label' => 'Plus de 1.5 buts', 'description' => '2 buts ou plus dans le match', 'odd' => 1.4],
                'under_1_5' => ['label' => 'Moins de 1.5 buts', 'description' => '0 ou 1 but dans le match', 'odd' => 3.0],
                'over_2_5' => ['label' => 'Plus de 2.5 buts', 'description' => '3 buts ou plus dans le match', 'odd' => 1.9],
                'under_2_5' => ['label' => 'Moins de 2.5 buts', 'description' => '0, 1 ou 2 buts dans le match', 'odd' => 2.0],
            ],
            'double_chance' => [
                '1X' => ['label' => '1X (Domicile ou Nul)', 'description' => 'L\'équipe à domicile gagne ou match nul', 'odd' => 1.3],
                'X2' => ['label' => 'X2 (Nul ou Extérieur)', 'description' => 'Match nul ou l\'équipe à l\'extérieur gagne', 'odd' => 1.3],
                '12' => ['label' => '12 (Domicile ou Extérieur)', 'description' => 'L\'une des deux équipes gagne (pas de nul)', 'odd' => 1.2],
            ],
            'first_half' => [
                'home_win' => ['label' => 'Domicile gagne 1ère MT', 'description' => 'L\'équipe à domicile mène à la mi-temps', 'odd' => 2.5],
                'draw' => ['label' => 'Nul à la mi-temps', 'description' => 'Score nul à la mi-temps', 'odd' => 2.2],
                'away_win' => ['label' => 'Extérieur gagne 1ère MT', 'description' => 'L\'équipe à l\'extérieur mène à la mi-temps', 'odd' => 2.5],
            ],
            'exact_score' => [
                '1_0' => ['label' => '1-0', 'description' => 'Score final exact : 1-0', 'odd' => 8.0],
                '2_0' => ['label' => '2-0', 'description' => 'Score final exact : 2-0', 'odd' => 10.0],
                '2_1' => ['label' => '2-1', 'description' => 'Score final exact : 2-1', 'odd' => 9.0],
                '0_0' => ['label' => '0-0', 'description' => 'Score final exact : 0-0', 'odd' => 12.0],
                '1_1' => ['label' => '1-1', 'description' => 'Score final exact : 1-1', 'odd' => 7.0],
                '0_1' => ['label' => '0-1', 'description' => 'Score final exact : 0-1', 'odd' => 8.0],
                '0_2' => ['label' => '0-2', 'description' => 'Score final exact : 0-2', 'odd' => 10.0],
                '1_2' => ['label' => '1-2', 'description' => 'Score final exact : 1-2', 'odd' => 9.0],
            ],
            'handicap' => [
                'home_minus_1' => ['label' => 'Domicile -1', 'description' => 'L\'équipe à domicile gagne avec 2 buts d\'écart minimum', 'odd' => 3.5],
                'home_minus_2' => ['label' => 'Domicile -2', 'description' => 'L\'équipe à domicile gagne avec 3 buts d\'écart minimum', 'odd' => 6.0],
                'away_minus_1' => ['label' => 'Extérieur -1', 'description' => 'L\'équipe à l\'extérieur gagne avec 2 buts d\'écart minimum', 'odd' => 3.5],
                'away_minus_2' => ['label' => 'Extérieur -2', 'description' => 'L\'équipe à l\'extérieur gagne avec 3 buts d\'écart minimum', 'odd' => 6.0],
            ],
        ];
    }

    /**
     * Obtenir les cotes pour ce match (personnalisées ou par défaut)
     */
    public function getOdds(): array
    {
        return $this->odds ?? self::getDefaultOdds();
    }

    /**
     * Convertir les cotes au format Repeater pour Filament (édition)
     */
    public function getOddsForForm(): ?array
    {
        if (!$this->odds) {
            return null;
        }

        $forForm = [];

        foreach ($this->odds as $market => $options) {
            $forForm[$market] = [];

            foreach ($options as $key => $data) {
                $forForm[$market][] = [
                    'key' => $key,
                    'label' => $data['label'] ?? '',
                    'description' => $data['description'] ?? '',
                    'odd' => $data['odd'] ?? 1.0,
                ];
            }
        }

        return $forForm;
    }
}
