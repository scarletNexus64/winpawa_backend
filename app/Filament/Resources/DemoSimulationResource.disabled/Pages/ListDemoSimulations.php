<?php

namespace App\Filament\Resources\DemoSimulationResource\Pages;

use App\Filament\Resources\DemoSimulationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDemoSimulations extends ListRecords
{
    protected static string $resource = DemoSimulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
