<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use Filament\Widgets\Widget;

class TopGamesWidget extends Widget
{
    protected static ?int $sort = 2;

    protected static string $view = 'filament.widgets.top-games-widget';

    protected int | string | array $columnSpan = 'full';

    public function getGames()
    {
        // Récupérer les 5 jeux les plus joués
        $topGames = Game::withCount('bets')
            ->where('is_active', true)
            ->orderBy('bets_count', 'desc')
            ->limit(5)
            ->get();

        // Si moins de 5 jeux ou aucun jeu n'a de paris, prendre les featured ou les premiers
        if ($topGames->count() < 5 || $topGames->sum('bets_count') == 0) {
            $topGames = Game::where('is_active', true)
                ->where(function ($query) {
                    $query->where('is_featured', true)
                        ->orWhereNotNull('id');
                })
                ->orderBy('is_featured', 'desc')
                ->orderBy('sort_order', 'asc')
                ->limit(5)
                ->get();
        }

        return $topGames;
    }
}
