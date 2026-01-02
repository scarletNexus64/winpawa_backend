<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemoConfiguration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'is_active',
        'selected_games',
        'period_type',
        'start_date',
        'end_date',
        'win_rate',
        'min_bet',
        'max_bet',
        'min_win_multiplier',
        'max_win_multiplier',
        'daily_bet_count',
        'configuration',
        'description',
    ];

    protected $casts = [
        'selected_games' => 'array',
        'configuration' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'win_rate' => 'decimal:2',
        'min_bet' => 'decimal:2',
        'max_bet' => 'decimal:2',
        'min_win_multiplier' => 'decimal:2',
        'max_win_multiplier' => 'decimal:2',
    ];

    /**
     * Get the user that owns this demo configuration
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all simulated data for this configuration
     */
    public function simulatedData(): HasMany
    {
        return $this->hasMany(DemoSimulatedData::class);
    }

    /**
     * Get selected games
     */
    public function games()
    {
        if (empty($this->selected_games)) {
            return collect();
        }

        return Game::whereIn('id', $this->selected_games)->get();
    }

    /**
     * Check if configuration is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        $isAfterStart = $now->greaterThanOrEqualTo($this->start_date);
        $isBeforeEnd = !$this->end_date || $now->lessThanOrEqualTo($this->end_date);

        return $isAfterStart && $isBeforeEnd;
    }

    /**
     * Scope to get only active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get configurations for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
