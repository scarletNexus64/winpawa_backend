<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameModuleResource\Pages;
use App\Filament\Resources\GameModuleResource\RelationManagers;
use App\Models\GameModule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class GameModuleResource extends Resource
{
    protected static ?string $model = GameModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Gestion des Jeux';

    protected static ?string $navigationLabel = 'Modules de Jeux';

    protected static ?string $modelLabel = 'Module';

    protected static ?string $pluralModelLabel = 'Modules';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Module')
                    ->description('Configurez les informations de base du module de jeux')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du module')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('icon')
                            ->label('Icône Heroicon')
                            ->helperText('Ex: heroicon-o-puzzle-piece')
                            ->default('heroicon-o-puzzle-piece')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Configuration')
                    ->description('Paramètres de verrouillage et d\'affichage')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Toggle::make('is_locked')
                            ->label('Module verrouillé')
                            ->helperText('Si verrouillé, le module apparaîtra avec un cadenas dans le menu')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Module actif')
                            ->helperText('Si désactivé, le module n\'apparaîtra pas dans le menu')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->helperText('Ordre d\'affichage dans le menu (0 en premier)'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom du module')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (GameModule $record) => $record->description),

                Tables\Columns\TextColumn::make('games_count')
                    ->label('Nombre de jeux')
                    ->counts('games')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_locked')
                    ->label('Verrouillé')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable()
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_locked')
                    ->label('Verrouillé'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_lock')
                    ->label(fn (GameModule $record) => $record->is_locked ? 'Déverrouiller' : 'Verrouiller')
                    ->icon(fn (GameModule $record) => $record->is_locked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (GameModule $record) => $record->is_locked ? 'success' : 'danger')
                    ->action(fn (GameModule $record) => $record->update(['is_locked' => !$record->is_locked])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
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
            'index' => Pages\ListGameModules::route('/'),
            'create' => Pages\CreateGameModule::route('/create'),
            'edit' => Pages\EditGameModule::route('/{record}/edit'),
        ];
    }
}
