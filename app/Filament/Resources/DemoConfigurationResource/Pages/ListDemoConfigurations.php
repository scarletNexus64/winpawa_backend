<?php

namespace App\Filament\Resources\DemoConfigurationResource\Pages;

use App\Filament\Resources\DemoConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDemoConfigurations extends ListRecords
{
    protected static string $resource = DemoConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
