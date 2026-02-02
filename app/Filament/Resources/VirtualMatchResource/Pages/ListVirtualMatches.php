<?php

namespace App\Filament\Resources\VirtualMatchResource\Pages;

use App\Filament\Resources\VirtualMatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVirtualMatches extends ListRecords
{
    protected static string $resource = VirtualMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
