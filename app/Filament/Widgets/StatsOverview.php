<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Bet;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        // Calcul des soldes totaux
        $totalFreemoPay = Wallet::where('currency', 'XAF')->sum('main_balance');
        $totalCrypto = Wallet::where('currency', '!=', 'XAF')->sum('main_balance');

        // Stats utilisateurs
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();

        // Stats paris aujourd'hui
        $betsToday = Bet::whereDate('created_at', today())->count();
        $betsTodayAmount = Bet::whereDate('created_at', today())->sum('amount');

        // Profit aujourd'hui (revenus - payouts)
        $todayRevenue = Bet::whereDate('created_at', today())->sum('amount');
        $todayPayout = Bet::whereDate('created_at', today())
            ->where('is_winner', true)
            ->sum('payout');
        $todayProfit = $todayRevenue - $todayPayout;

        // Dépôts aujourd'hui
        $depositsToday = Transaction::deposits()
            ->whereDate('created_at', today())
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        // Retraits aujourd'hui
        $withdrawalsToday = Transaction::withdrawals()
            ->whereDate('created_at', today())
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');

        return [
            Stat::make('Solde Total FreemoPay', number_format($totalFreemoPay, 0, ',', ' ') . ' FCFA')
                ->description('Solde principal FCFA')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Solde Total Crypto', number_format($totalCrypto, 2, ',', ' '))
                ->description('Solde crypto-monnaies')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Total Utilisateurs', number_format($totalUsers))
                ->description('Inscrits sur la plateforme')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 3, 4, 5, 6, 3, 5, 8])
                ->color('primary'),

            Stat::make('Nouveaux Utilisateurs', $newUsersToday)
                ->description('Inscriptions du jour')
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart([2, 4, 6, 8, 5, 3, 7, 4])
                ->color('info'),

            Stat::make('Paris Aujourd\'hui', number_format($betsToday))
                ->description(number_format($betsTodayAmount, 0, ',', ' ') . ' FCFA misés')
                ->descriptionIcon('heroicon-m-ticket')
                ->chart([10, 15, 12, 18, 14, 20, 16, 22])
                ->color('warning'),

            Stat::make('Profit Aujourd\'hui', number_format($todayProfit, 0, ',', ' ') . ' FCFA')
                ->description($todayProfit >= 0 ? 'Bénéfice du jour' : 'Perte du jour')
                ->descriptionIcon($todayProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([5, 8, 12, 15, 10, 18, 14, 20])
                ->color($todayProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Dépôts Aujourd\'hui', number_format($depositsToday, 0, ',', ' ') . ' FCFA')
                ->description('Dépôts complétés')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Retraits Aujourd\'hui', number_format($withdrawalsToday, 0, ',', ' ') . ' FCFA')
                ->description('Retraits complétés')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),
        ];
    }
}
