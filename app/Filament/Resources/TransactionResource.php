<?php

namespace App\Filament\Resources;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de la transaction')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options(collect(TransactionType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Montant')
                            ->numeric()
                            ->prefix('FCFA')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(collect(TransactionStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required(),

                        Forms\Components\Select::make('payment_method')
                            ->label('Méthode de paiement')
                            ->options([
                                'mtn_momo' => 'MTN Mobile Money',
                                'orange_money' => 'Orange Money',
                            ]),

                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Référence paiement'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => TransactionType::from($state)->label())
                    ->color(fn ($state) => TransactionType::from($state)->color()),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('XAF')
                    ->sortable()
                    ->color(fn (Transaction $record) => $record->type === TransactionType::DEPOSIT ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Méthode')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'mtn_momo' => 'MTN MoMo',
                        'orange_money' => 'Orange Money',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => TransactionStatus::from($state)->label())
                    ->color(fn ($state) => TransactionStatus::from($state)->color()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(collect(TransactionType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(collect(TransactionStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Méthode')
                    ->options([
                        'mtn_momo' => 'MTN MoMo',
                        'orange_money' => 'Orange Money',
                    ]),

                Tables\Filters\Filter::make('today')
                    ->label('Aujourd\'hui')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Transaction $record) => $record->status === TransactionStatus::PENDING)
                    ->requiresConfirmation()
                    ->action(fn (Transaction $record) => $record->markAsCompleted()),

                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Transaction $record) => $record->status === TransactionStatus::PENDING)
                    ->requiresConfirmation()
                    ->action(fn (Transaction $record) => $record->markAsFailed('Rejeté par admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}
