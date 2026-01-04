<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extraire les données du wallet
        if (isset($data['wallet'])) {
            $walletData = $data['wallet'];
            unset($data['wallet']);

            // Sauvegarder les données du wallet
            if ($this->record->wallet) {
                $this->record->wallet->update([
                    'main_balance' => $walletData['main_balance'] ?? $this->record->wallet->main_balance,
                    'bonus_balance' => $walletData['bonus_balance'] ?? $this->record->wallet->bonus_balance,
                ]);
            }
        }

        return $data;
    }
}
