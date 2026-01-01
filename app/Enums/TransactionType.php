<?php

namespace App\Enums;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case BET = 'bet';
    case WIN = 'win';
    case BONUS = 'bonus';
    case AFFILIATE = 'affiliate';
    case REFUND = 'refund';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Dépôt',
            self::WITHDRAWAL => 'Retrait',
            self::BET => 'Mise',
            self::WIN => 'Gain',
            self::BONUS => 'Bonus',
            self::AFFILIATE => 'Commission Affiliation',
            self::REFUND => 'Remboursement',
            self::ADJUSTMENT => 'Ajustement',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEPOSIT => 'success',
            self::WITHDRAWAL => 'warning',
            self::BET => 'danger',
            self::WIN => 'success',
            self::BONUS => 'info',
            self::AFFILIATE => 'primary',
            self::REFUND => 'secondary',
            self::ADJUSTMENT => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DEPOSIT => 'heroicon-o-arrow-down-circle',
            self::WITHDRAWAL => 'heroicon-o-arrow-up-circle',
            self::BET => 'heroicon-o-ticket',
            self::WIN => 'heroicon-o-trophy',
            self::BONUS => 'heroicon-o-gift',
            self::AFFILIATE => 'heroicon-o-users',
            self::REFUND => 'heroicon-o-arrow-uturn-left',
            self::ADJUSTMENT => 'heroicon-o-adjustments-horizontal',
        };
    }
}
