<?php

namespace App\Filament\Resources\GameResource\Widgets;

use App\Models\Game;
use App\Models\GameCategory;
use App\Models\GameModule;
use Filament\Widgets\Widget;

class GamesCategoriesCards extends Widget
{
    protected static string $view = 'filament.resources.game-resource.widgets.games-categories-cards';

    protected int | string | array $columnSpan = 'full';

    public function getCategories(): array
    {
        $casinoModule = GameModule::where('slug', 'jeux-casino')->first();

        if (!$casinoModule) {
            return [];
        }

        // Récupérer toutes les catégories actives
        $activeCategories = GameCategory::active()->ordered()->get();

        $categories = [];

        foreach ($activeCategories as $category) {
            // Compter les jeux de cette catégorie dans le module Casino
            $gamesInCategory = Game::where('module_id', $casinoModule->id)
                ->where('category_id', $category->id)
                ->where('is_active', true)
                ->get();

            $count = $gamesInCategory->count();

            if ($count > 0) {
                $categories[] = [
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color,
                    'count' => $count,
                    'games' => $gamesInCategory->take(6)->map(function ($game) {
                        return [
                            'name' => $game->name,
                            'icon' => $game->type?->icon(),
                        ];
                    })->toArray(),
                ];
            }
        }

        return $categories;
    }
}
