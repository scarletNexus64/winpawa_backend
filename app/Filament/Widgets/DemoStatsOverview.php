<?php

namespace App\Filament\Widgets;

use App\Models\DemoConfiguration;
use App\Models\DemoSimulatedData;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DemoStatsOverview extends BaseWidget
{
    protected static ?int $sort = 10;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        // Active configurations count
        $activeConfigs = DemoConfiguration::where('is_active', true)->count();
        $totalConfigs = DemoConfiguration::count();

        // Total simulated data points
        $totalDataPoints = DemoSimulatedData::count();

        // Total simulated bets
        $totalBets = DemoSimulatedData::sum('bet_count');
        $totalWins = DemoSimulatedData::sum('win_count');
        $totalLosses = DemoSimulatedData::sum('loss_count');

        // Total amounts
        $totalBetAmount = DemoSimulatedData::sum('total_bet_amount');
        $totalWinAmount = DemoSimulatedData::sum('total_win_amount');
        $totalLossAmount = DemoSimulatedData::sum('total_loss_amount');
        $netAmount = DemoSimulatedData::sum('net_amount');

        // Average win rate
        $avgWinRate = $totalBets > 0 ? ($totalWins / $totalBets) * 100 : 0;

        // Recent data for charts (last 7 days)
        $recentData = DemoSimulatedData::where('period_type', 'daily')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        $chartData = $recentData->reverse()->pluck('net_amount')->map(fn($val) => (float)$val)->toArray();

        return [
            Stat::make('Configurations Actives', $activeConfigs . ' / ' . $totalConfigs)
                ->description('Configurations de démo')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('info'),

            Stat::make('Points de Données', number_format($totalDataPoints))
                ->description('Jours de données simulées')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Paris Simulés', number_format($totalBets))
                ->description(number_format($totalWins) . ' gagnés, ' . number_format($totalLosses) . ' perdus')
                ->descriptionIcon('heroicon-m-ticket')
                ->chart(array_slice($chartData, 0, 8))
                ->color('warning'),

            Stat::make('Taux de Victoire Moyen', number_format($avgWinRate, 1) . '%')
                ->description('Taux de réussite global')
                ->descriptionIcon('heroicon-m-trophy')
                ->color($avgWinRate >= 50 ? 'success' : 'danger'),

            Stat::make('Montant Total Misé', number_format($totalBetAmount, 0, ',', ' ') . ' FCFA')
                ->description('Total des mises')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Gains Totaux', number_format($totalWinAmount, 0, ',', ' ') . ' FCFA')
                ->description('Montant des gains')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pertes Totales', number_format($totalLossAmount, 0, ',', ' ') . ' FCFA')
                ->description('Montant des pertes')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Résultat Net', number_format($netAmount, 0, ',', ' ') . ' FCFA')
                ->description($netAmount >= 0 ? 'Bénéfice global' : 'Perte globale')
                ->descriptionIcon($netAmount >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->chart($chartData)
                ->color($netAmount >= 0 ? 'success' : 'danger'),
        ];
    }
}
