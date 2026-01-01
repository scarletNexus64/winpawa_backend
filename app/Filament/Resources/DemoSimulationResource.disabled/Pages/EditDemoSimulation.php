<?php

namespace App\Filament\Resources\DemoSimulationResource\Pages;

use App\Filament\Resources\DemoSimulationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDemoSimulation extends EditRecord
{
    protected static string $resource = DemoSimulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
