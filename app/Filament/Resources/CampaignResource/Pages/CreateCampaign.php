<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        // Calculate total recipients
        if ($data['recipient_type'] === 'all') {
            $data['total_recipients'] = User::count();
        } else {
            $data['total_recipients'] = is_array($data['specific_users']) ? count($data['specific_users']) : 0;
        }

        return $data;
    }
}
