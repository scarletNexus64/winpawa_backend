<?php

namespace App\Filament\Resources\VirtualMatchResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BetsRelationManager extends RelationManager
{
    protected static string $relationship = 'bets';

    protected static ?string $title = 'Paris sur ce match';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference')
                    ->label('Référence')
                    ->disabled(),

                Forms\Components\Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->disabled(),

                Forms\Components\TextInput::make('amount')
                    ->label('Montant')
                    ->numeric()
                    ->prefix('FCFA')
                    ->disabled(),

                Forms\Components\TextInput::make('bet_type')
                    ->label('Type de pari')
                    ->disabled(),

                Forms\Components\TextInput::make('choice')
                    ->label('Choix')
                    ->disabled(),

                Forms\Components\TextInput::make('multiplier')
                    ->label('Multiplicateur')
                    ->numeric()
                    ->disabled(),

                Forms\Components\TextInput::make('payout')
                    ->label('Gain')
                    ->numeric()
                    ->prefix('FCFA')
                    ->disabled(),

                Forms\Components\Toggle::make('is_winner')
                    ->label('Gagnant')
                    ->disabled(),

                Forms\Components\TextInput::make('status')
                    ->label('Statut')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Joueur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('bet_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'result' => 'Résultat',
                        'score' => 'Score exact',
                        'both_score' => 'Les deux marquent',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('choice')
                    ->label('Choix')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'home_win' => 'Victoire domicile',
                        'away_win' => 'Victoire extérieur',
                        'draw' => 'Match nul',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Mise')
                    ->money('XOF')
                    ->sortable(),

                Tables\Columns\TextColumn::make('multiplier')
                    ->label('Cote')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('x')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payout')
                    ->label('Gain')
                    ->money('XOF')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_winner')
                    ->label('Gagnant')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'completed' => 'Traité',
                        'cancelled' => 'Annulé',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'completed' => 'Traité',
                        'cancelled' => 'Annulé',
                    ]),

                Tables\Filters\TernaryFilter::make('is_winner')
                    ->label('Résultat')
                    ->placeholder('Tous')
                    ->trueLabel('Gagnants')
                    ->falseLabel('Perdants'),
            ])
            ->headerActions([
                // Pas de création manuelle de paris depuis l'admin
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Pas de suppression en masse de paris
            ])
            ->emptyStateHeading('Aucun pari')
            ->emptyStateDescription('Aucun pari n\'a été placé sur ce match.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
