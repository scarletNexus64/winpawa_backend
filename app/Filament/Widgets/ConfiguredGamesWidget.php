<?php

namespace App\Filament\Widgets;

use App\Models\Game;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ConfiguredGamesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

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
            ->heading('✅ Jeux Configurés')
            ->description('Jeux prêts et opérationnels')
            ->query(
                Game::query()
                    ->where('is_configured', true)
                    ->orderBy('created_at', 'desc')
            )
            ->striped()
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('')
                    ->circular()
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

                Tables\Columns\TextColumn::make('bets_count')
                    ->label('Paris')
                    ->counts('bets')
                    ->badge()
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('')
                    ->icon('heroicon-m-pencil-square')
                    ->tooltip('Modifier')
                    ->url(fn (Game $record): string => route('filament.admin.resources.games.edit', ['record' => $record])),
            ])
            ->paginationPageOptions([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Aucun jeu configuré')
            ->emptyStateDescription('Commencez par configurer vos premiers jeux')
            ->emptyStateIcon('heroicon-o-puzzle-piece');
    }
}
