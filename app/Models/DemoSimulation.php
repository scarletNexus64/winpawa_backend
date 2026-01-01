<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoSimulation extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'scenario_type',
        'start_date',
        'end_date',
        'total_bets',
        'bets_won',
        'bets_lost',
        'games_played',
        'total_amount',
        'total_won',
        'total_lost',
        'net_profit',
        'scenario_config',
        'daily_data',
        'is_active',
        'is_preview',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_bets' => 'integer',
        'bets_won' => 'integer',
        'bets_lost' => 'integer',
        'games_played' => 'integer',
        'total_amount' => 'decimal:2',
        'total_won' => 'decimal:2',
        'total_lost' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'scenario_config' => 'array',
        'daily_data' => 'array',
        'is_active' => 'boolean',
        'is_preview' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Désactiver les autres simulations pour le même utilisateur quand on active une nouvelle
        static::saving(function ($simulation) {
            if ($simulation->is_active && !$simulation->is_preview) {
                self::where('user_id', $simulation->user_id)
                    ->where('id', '!=', $simulation->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getScenarioTypeLabelAttribute(): string
    {
        return match($this->scenario_type) {
            'gain' => 'Gains',
            'perte' => 'Pertes',
            'mixte' => 'Mixte',
            default => $this->scenario_type,
        };
    }

    public function getWinRateAttribute(): float
    {
        if ($this->total_bets === 0) return 0;
        return round(($this->bets_won / $this->total_bets) * 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_preview) {
            return 'Prévisualisation';
        }
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public static function getActiveForUser(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_preview', false)
            ->first();
    }
}
