<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_id',
        'home_team',
        'away_team',
        'home_logo',
        'away_logo',
        'league',
        'match_time',
        'status',
        'home_score',
        'away_score',
        'odds',
        'statistics',
        'is_featured',
    ];

    protected $casts = [
        'match_time' => 'datetime',
        'home_score' => 'integer',
        'away_score' => 'integer',
        'odds' => 'array',
        'statistics' => 'array',
        'is_featured' => 'boolean',
    ];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
            ->where('match_time', '>', now())
            ->orderBy('match_time');
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeFinished($query)
    {
        return $query->where('status', 'finished')
            ->orderBy('match_time', 'desc');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
