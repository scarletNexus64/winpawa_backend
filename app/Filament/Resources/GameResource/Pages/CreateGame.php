<?php

namespace App\Filament\Resources\GameResource\Pages;

use App\Filament\Resources\GameResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGame extends CreateRecord
{
    protected static string $resource = GameResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si une nouvelle image est uploadée, mettre à jour le champ image
        if (isset($data['new_image']) && !empty($data['new_image'])) {
            $data['image'] = $data['new_image'];
        }

        // Supprimer le champ temporaire
        unset($data['new_image']);

        return $data;
    }
}
