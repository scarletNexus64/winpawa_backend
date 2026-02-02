<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'referral_code',
        'referred_by',
        'is_active',
        'is_verified',
        'date_of_birth',
        'country',
        'currency',
        'city',
        'avatar',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'is_active'])
            ->logOnlyDirty();
    }

    // ==================== RELATIONS ====================

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function affiliateStats(): HasOne
    {
        return $this->hasOne(AffiliateStats::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(UserBonus::class);
    }

    public function virtualMatchBets(): HasMany
    {
        return $this->hasMany(VirtualMatchBet::class);
    }

    public function assignedGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'user_game')
            ->withTimestamps();
    }

    // ==================== ACCESSORS ====================

    public function getTotalBalanceAttribute(): float
    {
        return $this->wallet?->total_balance ?? 0;
    }

    public function getMainBalanceAttribute(): float
    {
        return $this->wallet?->main_balance ?? 0;
    }

    public function getBonusBalanceAttribute(): float
    {
        return $this->wallet?->bonus_balance ?? 0;
    }

    public function getReferralCountAttribute(): int
    {
        return $this->referrals()->count();
    }

    // ==================== METHODS ====================

    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    public function isAdult(): bool
    {
        if (!$this->date_of_birth) {
            return false;
        }
        return $this->date_of_birth->age >= 18;
    }

    public function canWithdraw(): bool
    {
        return $this->is_verified && $this->is_active;
    }

    public function canManageGame(Game $game): bool
    {
        // Super admin peut tout gérer
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Si l'utilisateur n'a pas de jeux assignés, il peut gérer tous les jeux
        if ($this->assignedGames()->count() === 0) {
            return true;
        }

        // Vérifier si le jeu est dans les jeux assignés
        return $this->assignedGames()->where('games.id', $game->id)->exists();
    }

    public function canManageGameById(int $gameId): bool
    {
        // Super admin peut tout gérer
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Si l'utilisateur n'a pas de jeux assignés, il peut gérer tous les jeux
        if ($this->assignedGames()->count() === 0) {
            return true;
        }

        // Vérifier si le jeu est dans les jeux assignés
        return $this->assignedGames()->where('games.id', $gameId)->exists();
    }

    public function getTotalWagered(): float
    {
        return $this->bets()->sum('amount');
    }

    public function hasMetWageringRequirements(): bool
    {
        $activeBonus = $this->bonuses()->where('status', 'active')->first();
        
        if (!$activeBonus) {
            return true;
        }

        $wagered = $this->getTotalWagered();
        $required = $activeBonus->wagering_requirement;

        return $wagered >= $required;
    }
}
