<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_category_id',
        'name',
        'slug',
        'type',
        'icon',
        'image',
        'description',
        'is_live',
        'is_virtual',
        'is_active',
        'match_duration',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'is_virtual' => 'boolean',
        'is_active' => 'boolean',
        'match_duration' => 'integer',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SportCategory::class, 'sport_category_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(SportMatch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }

    public function scopeVirtual($query)
    {
        return $query->where('is_virtual', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
