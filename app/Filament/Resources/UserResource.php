<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Utilisateurs';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->description('Informations de base de l\'utilisateur')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->prefix('+237'),

                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date de naissance')
                            ->maxDate(now()->subYears(18))
                            ->native(false),

                        Forms\Components\TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Sécurité')
                    ->description('Paramètres de connexion')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Compte actif')
                            ->default(true),

                        Forms\Components\Toggle::make('is_verified')
                            ->label('Compte vérifié')
                            ->default(false),
                    ])->columns(3),

                Forms\Components\Section::make('Rôles & Permissions')
                    ->description('Attribuer des rôles à l\'utilisateur pour gérer ses permissions')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Rôles')
                            ->helperText('Attribuez un ou plusieurs rôles pour donner des permissions à cet utilisateur')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Jeux Assignés')
                    ->description('Sélectionnez les jeux que cet utilisateur peut gérer (pour les partenaires/game managers)')
                    ->icon('heroicon-o-puzzle-piece')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Select::make('assignedGames')
                            ->label('Jeux autorisés')
                            ->helperText('Si aucun jeu n\'est sélectionné, l\'utilisateur aura accès à tous les jeux selon ses permissions')
                            ->relationship('assignedGames', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->type->label()})")
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Affiliation')
                    ->description('Code de parrainage')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Forms\Components\TextInput::make('referral_code')
                            ->label('Code de parrainage')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('referred_by')
                            ->label('Parrainé par')
                            ->relationship('referrer', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-o-phone'),

                Tables\Columns\TextColumn::make('wallet.main_balance')
                    ->label('Solde')
                    ->money('XAF')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('referrals_count')
                    ->label('Filleuls')
                    ->counts('referrals')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->color('warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('assignedGames.name')
                    ->label('Jeux assignés')
                    ->badge()
                    ->color('primary')
                    ->limitList(2)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Vérifié')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),

                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Vérifié'),

                Tables\Filters\Filter::make('has_balance')
                    ->label('Avec solde')
                    ->query(fn (Builder $query): Builder => $query->whereHas('wallet', fn ($q) => $q->where('main_balance', '>', 0))),

                Tables\Filters\Filter::make('created_today')
                    ->label('Inscrit aujourd\'hui')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (User $record) => $record->is_active ? 'Désactiver' : 'Activer')
                    ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
