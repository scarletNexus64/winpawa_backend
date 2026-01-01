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
        'duration', // en minutes (1, 3, 5)
        'status',
        'score_home',
        'score_away',
        'result', // home_win, away_win, draw
        'starts_at',
        'ends_at',
        'rng_seed',
        'metadata',
    ];

    protected $casts = [
        'duration' => 'integer',
        'status' => VirtualMatchStatus::class,
        'score_home' => 'integer',
        'score_away' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
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
        });
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
        return $this->status === VirtualMatchStatus::UPCOMING 
               && $this->starts_at > now()->addSeconds(10);
    }

    // ==================== METHODS ====================

    public static function generateReference(): string
    {
        return 'VM-' . strtoupper(Str::random(8));
    }

    public function start(): void
    {
        $this->status = VirtualMatchStatus::LIVE;
        $this->save();
    }

    public function complete(): void
    {
        $this->generateResult();
        $this->status = VirtualMatchStatus::COMPLETED;
        $this->ends_at = now();
        $this->save();

        // Traiter tous les paris
        $this->processBets();
    }

    protected function generateResult(): void
    {
        $hash = hash('sha256', $this->rng_seed . $this->id);
        
        // Générer les scores (0-5 pour chaque équipe)
        $homeScore = hexdec(substr($hash, 0, 2)) % 6;
        $awayScore = hexdec(substr($hash, 2, 2)) % 6;
        
        $this->score_home = $homeScore;
        $this->score_away = $awayScore;
        
        if ($homeScore > $awayScore) {
            $this->result = 'home_win';
        } elseif ($awayScore > $homeScore) {
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
}
