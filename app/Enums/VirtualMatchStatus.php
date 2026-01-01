<?php

namespace App\Enums;

enum VirtualMatchStatus: string
{
    case UPCOMING = 'upcoming';
    case LIVE = 'live';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::UPCOMING => 'À venir',
            self::LIVE => 'En direct',
            self::COMPLETED => 'Terminé',
            self::CANCELLED => 'Annulé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UPCOMING => 'info',
            self::LIVE => 'success',
            self::COMPLETED => 'gray',
            self::CANCELLED => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::UPCOMING => 'heroicon-o-clock',
            self::LIVE => 'heroicon-o-signal',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }
}
