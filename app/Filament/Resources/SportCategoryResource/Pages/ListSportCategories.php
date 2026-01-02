<?php

namespace App\Filament\Resources\SportCategoryResource\Pages;

use App\Filament\Resources\SportCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSportCategories extends ListRecords
{
    protected static string $resource = SportCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
