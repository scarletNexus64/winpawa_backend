<?php

namespace App\Enums;

enum BetStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::COMPLETED => 'Terminé',
            self::CANCELLED => 'Annulé',
            self::REFUNDED => 'Remboursé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::CANCELLED => 'gray',
            self::REFUNDED => 'info',
        };
    }
}
