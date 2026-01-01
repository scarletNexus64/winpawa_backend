<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'name',
        'code',
        'locale',
        'flag_emoji',
        'is_active',
        'is_default',
        'is_rtl',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_rtl' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Ensure only one default language
        static::saving(function ($language) {
            if ($language->is_default) {
                self::where('id', '!=', $language->id)->update(['is_default' => false]);
            }
        });
    }

    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->where('is_active', true)->first();
    }

    public static function getActive()
    {
        return self::where('is_active', true)->orderBy('sort_order')->get();
    }

    public static function getByCode(string $code): ?self
    {
        return self::where('code', $code)->where('is_active', true)->first();
    }
}
