<?php

namespace App\Filament\Resources\DemoSimulationResource\Pages;

use App\Filament\Resources\DemoSimulationResource;
use App\Services\DemoSimulationService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewDemoSimulation extends ViewRecord
{
    protected static string $resource = DemoSimulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('activate')
                ->label('Activer cette simulation')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => !$this->record->is_active)
                ->action(function () {
                    $service = new DemoSimulationService();
                    $service->activateSimulation($this->record);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\Action::make('deactivate')
                ->label('Désactiver')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->is_active)
                ->action(function () {
                    $service = new DemoSimulationService();
                    $service->deactivateSimulation($this->record);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\EditAction::make()
                ->visible(fn () => !$this->record->is_active),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations générales')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nom du scénario'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Utilisateur cible'),
                        Infolists\Components\TextEntry::make('scenario_type_label')
                            ->label('Type de scénario')
                            ->badge()
                            ->color(fn () => match($this->record->scenario_type) {
                                'gain' => 'success',
                                'perte' => 'danger',
                                'mixte' => 'info',
                            }),
                        Infolists\Components\TextEntry::make('status_label')
                            ->label('Statut')
                            ->badge()
                            ->color(fn () => match (true) {
                                $this->record->is_preview => 'gray',
                                $this->record->is_active => 'success',
                                default => 'warning',
                            }),
                    ])->columns(2),

                Infolists\Components\Section::make('Période')
                    ->schema([
                        Infolists\Components\TextEntry::make('start_date')
                            ->label('Date de début')
                            ->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('end_date')
                            ->label('Date de fin')
                            ->date('d/m/Y'),
                    ])->columns(2),

                Infolists\Components\Section::make('Statistiques globales')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_bets')
                            ->label('Total Paris')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('bets_won')
                            ->label('Paris Gagnés')
                            ->numeric()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('bets_lost')
                            ->label('Paris Perdus')
                            ->numeric()
                            ->color('danger'),
                        Infolists\Components\TextEntry::make('win_rate')
                            ->label('Taux de Réussite')
                            ->suffix('%')
                            ->color(fn () => $this->record->win_rate >= 50 ? 'success' : 'danger'),
                        Infolists\Components\TextEntry::make('games_played')
                            ->label('Jeux Joués')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Montant Total')
                            ->money('XOF'),
                        Infolists\Components\TextEntry::make('total_won')
                            ->label('Total Gagné')
                            ->money('XOF')
                            ->color('success'),
                        Infolists\Components\TextEntry::make('total_lost')
                            ->label('Total Perdu')
                            ->money('XOF')
                            ->color('danger'),
                        Infolists\Components\TextEntry::make('net_profit')
                            ->label('Profit Net')
                            ->money('XOF')
                            ->color(fn () => $this->record->net_profit >= 0 ? 'success' : 'danger')
                            ->weight('bold'),
                    ])->columns(3),
            ]);
    }
}
