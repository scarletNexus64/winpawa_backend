<?php

namespace App\Filament\Resources\SportCategoryResource\Pages;

use App\Filament\Resources\SportCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSportCategory extends EditRecord
{
    protected static string $resource = SportCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
