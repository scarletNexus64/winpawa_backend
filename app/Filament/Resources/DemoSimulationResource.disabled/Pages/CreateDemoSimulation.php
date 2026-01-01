<?php

namespace App\Filament\Resources\DemoSimulationResource\Pages;

use App\Filament\Resources\DemoSimulationResource;
use App\Services\DemoSimulationService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDemoSimulation extends CreateRecord
{
    protected static string $resource = DemoSimulationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        // Générer les données de simulation
        $service = new DemoSimulationService();
        $simulation = new \App\Models\DemoSimulation($data);
        $generatedData = $service->generateSimulationData($simulation);

        // Fusionner les données générées
        return array_merge($data, $generatedData);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
