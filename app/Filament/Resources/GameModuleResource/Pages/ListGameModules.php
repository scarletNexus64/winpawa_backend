<?php

namespace App\Filament\Resources\GameModuleResource\Pages;

use App\Filament\Resources\GameModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGameModules extends ListRecords
{
    protected static string $resource = GameModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
