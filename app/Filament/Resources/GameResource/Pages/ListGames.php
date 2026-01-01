<?php
// ListGames.php
namespace App\Filament\Resources\GameResource\Pages;

use App\Enums\GameType;
use App\Filament\Resources\GameResource;
use App\Models\Game;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\DB;

class ListGames extends ListRecords
{
    protected static string $resource = GameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouveau jeu'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GameResource\Widgets\GamesStatsOverview::class,
            GameResource\Widgets\GamesCategoriesCards::class,
        ];
    }
}
