<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Models\Wallet;
use App\Models\AffiliateStats;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['referral_code'] = User::generateReferralCode();
        return $data;
    }

    protected function afterCreate(): void
    {
        // Créer le wallet
        Wallet::create([
            'user_id' => $this->record->id,
            'main_balance' => 0,
            'bonus_balance' => 0,
            'affiliate_balance' => 0,
        ]);

        // Créer les stats d'affiliation
        AffiliateStats::create([
            'user_id' => $this->record->id,
        ]);

        // Assigner le rôle utilisateur
        $this->record->assignRole('user');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
