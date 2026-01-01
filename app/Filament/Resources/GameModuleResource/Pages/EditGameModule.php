<?php

namespace App\Filament\Resources\GameModuleResource\Pages;

use App\Filament\Resources\GameModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGameModule extends EditRecord
{
    protected static string $resource = GameModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
