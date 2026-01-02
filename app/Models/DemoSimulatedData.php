<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoSimulatedData extends Model
{
    protected $fillable = [
        'demo_configuration_id',
        'user_id',
        'game_id',
        'date',
        'period_type',
        'total_bet_amount',
        'total_win_amount',
        'total_loss_amount',
        'net_amount',
        'bet_count',
        'win_count',
        'loss_count',
        'win_rate_actual',
        'hourly_data',
        'game_breakdown',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'total_bet_amount' => 'decimal:2',
        'total_win_amount' => 'decimal:2',
        'total_loss_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'win_rate_actual' => 'decimal:2',
        'hourly_data' => 'array',
        'game_breakdown' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the demo configuration that owns this simulated data
     */
    public function demoConfiguration(): BelongsTo
    {
        return $this->belongsTo(DemoConfiguration::class);
    }

    /**
     * Get the user that owns this simulated data
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the game for this simulated data
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Scope to get data for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get data for a specific period type
     */
    public function scopePeriodType($query, $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Scope to get data for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get data for a specific configuration
     */
    public function scopeForConfiguration($query, $configId)
    {
        return $query->where('demo_configuration_id', $configId);
    }
}
