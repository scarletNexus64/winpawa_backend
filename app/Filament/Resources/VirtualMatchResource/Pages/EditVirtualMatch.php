<?php

namespace App\Filament\Resources\VirtualMatchResource\Pages;

use App\Filament\Resources\VirtualMatchResource;
use App\Events\VirtualMatchEdited;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVirtualMatch extends EditRecord
{
    protected static string $resource = VirtualMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Hook appelé après la modification du match
     */
    protected function afterSave(): void
    {
        // Déclencher l'événement WebSocket pour notifier le frontend
        event(new VirtualMatchEdited($this->record));

        \Log::info('✏️ [Filament] Match virtuel modifié et événement déclenché', [
            'match_id' => $this->record->id,
            'reference' => $this->record->reference,
            'status' => $this->record->status->value,
        ]);
    }
}
