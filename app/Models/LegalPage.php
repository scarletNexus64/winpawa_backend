<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $fillable = [
        'type',
        'title',
        'content',
        'is_active',
        'last_updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($page) {
            $page->last_updated_at = now();
        });
    }

    public static function getByType(string $type): ?self
    {
        return self::where('type', $type)->where('is_active', true)->first();
    }
}
