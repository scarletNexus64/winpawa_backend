<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_locked',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'module_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
