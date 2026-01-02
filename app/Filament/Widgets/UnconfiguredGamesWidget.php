<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UnconfiguredGamesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'sm' => 'full',
        'md' => 'full',
        'lg' => 1,
        'xl' => 1,
    ];

    protected static string $maxHeight = '600px';

    public function table(Table $table): Table
    {
        return $table
            ->heading('⚠️ Jeux Non Configurés')
            ->description('Ces jeux nécessitent une configuration avant d\'être activés')
            ->query(
                Game::query()
                    ->where('is_configured', false)
                    ->orderBy('created_at', 'desc')
            )
            ->striped()
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('')
                    ->circular()
                    ->getStateUsing(function ($record) {
                        return $record->image ? url('images/' . $record->image) : url('/images/logo.png');
                    })
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Game $record) => $record->type?->icon() . ' ' . $record->type?->label())
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('configure')
                    ->label('')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->color('warning')
                    ->tooltip('Configurer')
                    ->url(fn (Game $record): string => route('filament.admin.resources.games.edit', ['record' => $record])),
            ])
            ->paginationPageOptions([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Tous les jeux sont configurés')
            ->emptyStateDescription('Félicitations! Tous vos jeux ont été configurés.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
