<?php

namespace App\Filament\Resources\VirtualMatchResource\Pages;

use App\Filament\Resources\VirtualMatchResource;
use App\Events\VirtualMatchCreated;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVirtualMatch extends CreateRecord
{
    protected static string $resource = VirtualMatchResource::class;

    /**
     * Hook appelé après la création du match
     */
    protected function afterCreate(): void
    {
        // Déclencher l'événement WebSocket pour notifier le frontend
        event(new VirtualMatchCreated($this->record));

        \Log::info('🆕 [Filament] Match virtuel créé et événement déclenché', [
            'match_id' => $this->record->id,
            'reference' => $this->record->reference,
            'status' => $this->record->status->value,
        ]);
    }
}
