<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate_to_xaf',
        'is_active',
    ];

    protected $casts = [
        'rate_to_xaf' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    /**
     * Convertit un montant d'une devise vers XAF
     */
    public function convertToXAF(float $amount): float
    {
        return $amount * $this->rate_to_xaf;
    }

    /**
     * Convertit un montant depuis XAF vers cette devise
     */
    public function convertFromXAF(float $amount): float
    {
        if ($this->rate_to_xaf == 0) {
            return 0;
        }
        return $amount / $this->rate_to_xaf;
    }

    /**
     * Obtient la devise par code
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))->where('is_active', true)->first();
    }
}
