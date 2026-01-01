<?php

namespace App\Filament\Resources\GameResource\Widgets;

use App\Models\Game;
use App\Models\GameModule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GamesStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Récupérer le module Casino
        $casinoModule = GameModule::where('slug', 'jeux-casino')->first();

        if (!$casinoModule) {
            return [];
        }

        // Compter tous les jeux du module Casino
        $totalGames = Game::where('module_id', $casinoModule->id)->count();

        // Compter les jeux en vedette
        $featuredGames = Game::where('module_id', $casinoModule->id)
            ->where('is_featured', true)
            ->count();

        return [
            Stat::make('Total Jeux', $totalGames)
                ->description('Nombre total de jeux disponibles')
                ->descriptionIcon('heroicon-m-puzzle-piece')
                ->color('primary')
                ->chart([7, 10, 12, 12, 12, 12, 12]),

            Stat::make('Jeux en Vedette', $featuredGames)
                ->description('Jeux mis en avant')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning')
                ->chart([3, 4, 5, 5, 5, 5, 5]),
        ];
    }
}
