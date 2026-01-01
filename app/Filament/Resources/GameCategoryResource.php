<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameCategoryResource\Pages;
use App\Filament\Resources\GameCategoryResource\RelationManagers;
use App\Models\GameCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class GameCategoryResource extends Resource
{
    protected static ?string $model = GameCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Gestion des Jeux';

    protected static ?string $navigationLabel = 'Catégories de Jeux';

    protected static ?string $modelLabel = 'Catégorie';

    protected static ?string $pluralModelLabel = 'Catégories';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la Catégorie')
                    ->description('Configurez les informations de base de la catégorie')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la catégorie')
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
                            ->label('Icône (emoji)')
                            ->helperText('Utilisez un emoji comme 🎰, 🔮, ⚡, 🧠, etc.')
                            ->default('🎮')
                            ->required()
                            ->maxLength(10),

                        Forms\Components\Select::make('color')
                            ->label('Couleur')
                            ->options([
                                'primary' => 'Primary (Bleu)',
                                'success' => 'Success (Vert)',
                                'danger' => 'Danger (Rouge)',
                                'warning' => 'Warning (Orange)',
                                'info' => 'Info (Cyan)',
                                'gray' => 'Gray (Gris)',
                            ])
                            ->default('primary')
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Paramètres')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icône')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (GameCategory $record) => $record->description),

                Tables\Columns\TextColumn::make('color')
                    ->label('Couleur')
                    ->badge()
                    ->color(fn (string $state): string => $state),

                Tables\Columns\TextColumn::make('games_count')
                    ->label('Nombre de jeux')
                    ->counts('games')
                    ->badge()
                    ->color('primary')
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListGameCategories::route('/'),
            'create' => Pages\CreateGameCategory::route('/create'),
            'edit' => Pages\EditGameCategory::route('/{record}/edit'),
        ];
    }
}
