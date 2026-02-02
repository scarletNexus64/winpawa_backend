<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SportResource\Pages;
use App\Filament\Resources\SportResource\RelationManagers;
use App\Models\Sport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SportResource extends Resource
{
    protected static ?string $model = Sport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Select::make('sport_category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'football' => 'Football',
                                'basketball' => 'Basketball',
                                'tennis' => 'Tennis',
                                'volleyball' => 'Volleyball',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('icon')
                            ->placeholder('heroicon-o-trophy'),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('sports')
                            ->imageEditor()
                            ->maxSize(2048),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Paramètres')
                    ->schema([
                        Forms\Components\Toggle::make('is_live')
                            ->label('Sport en direct')
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_virtual')
                            ->label('Sport virtuel')
                            ->default(false)
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('match_duration')
                            ->label('Durée du match (minutes)')
                            ->required()
                            ->numeric()
                            ->default(90)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                // Section Virtual Match Configuration (visible seulement si is_virtual = true)
                Forms\Components\Section::make('Configuration Virtual Match')
                    ->schema([
                        Forms\Components\Select::make('settings.virtual_config.game_type')
                            ->label('Type de jeu')
                            ->options([
                                'fifa' => 'FIFA',
                                'nba2k' => 'NBA 2K',
                                'tennis_world' => 'Tennis World Tour',
                            ])
                            ->default('fifa')
                            ->required(),

                        Forms\Components\CheckboxList::make('settings.virtual_config.match_durations')
                            ->label('Durées de match disponibles (minutes)')
                            ->options([
                                '1' => '1 minute',
                                '3' => '3 minutes',
                                '5' => '5 minutes',
                            ])
                            ->default(['3', '5'])
                            ->required()
                            ->columns(3),

                        Forms\Components\Select::make('settings.virtual_config.default_duration')
                            ->label('Durée par défaut')
                            ->options([
                                1 => '1 minute',
                                3 => '3 minutes',
                                5 => '5 minutes',
                            ])
                            ->default(3)
                            ->required(),

                        Forms\Components\TextInput::make('settings.virtual_config.betting_cutoff_seconds')
                            ->label('Fermeture des paris (secondes avant le match)')
                            ->numeric()
                            ->default(30)
                            ->required(),

                        Forms\Components\Section::make('Cotes de paris')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.virtual_config.odds.home_win')
                                            ->label('Victoire domicile')
                                            ->numeric()
                                            ->step(0.1)
                                            ->default(2.0)
                                            ->required(),

                                        Forms\Components\TextInput::make('settings.virtual_config.odds.away_win')
                                            ->label('Victoire extérieur')
                                            ->numeric()
                                            ->step(0.1)
                                            ->default(2.0)
                                            ->required(),

                                        Forms\Components\TextInput::make('settings.virtual_config.odds.draw')
                                            ->label('Match nul')
                                            ->numeric()
                                            ->step(0.1)
                                            ->default(3.5)
                                            ->required(),

                                        Forms\Components\TextInput::make('settings.virtual_config.odds.score_exact')
                                            ->label('Score exact')
                                            ->numeric()
                                            ->step(0.1)
                                            ->default(10.0)
                                            ->required(),

                                        Forms\Components\TextInput::make('settings.virtual_config.odds.both_score')
                                            ->label('Les deux marquent')
                                            ->numeric()
                                            ->step(0.1)
                                            ->default(1.8)
                                            ->required(),

                                        Forms\Components\TextInput::make('settings.virtual_config.odds.over_2_5')
                                            ->label('Plus de 2.5 buts')
                                            ->numeric()
                                            ->step(0.1)
                                            ->default(1.9),

                                        Forms\Components\TextInput::make('settings.virtual_config.odds.under_2_5')
                                            ->label('Moins de 2.5 buts')
                                            ->numeric()
                                            ->step(0.1)
                                            ->default(1.9),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Section::make('Génération automatique')
                            ->schema([
                                Forms\Components\TextInput::make('settings.virtual_config.match_generation.interval_minutes')
                                    ->label('Intervalle entre les matchs (minutes)')
                                    ->numeric()
                                    ->default(5)
                                    ->required(),

                                Forms\Components\TextInput::make('settings.virtual_config.match_generation.matches_ahead')
                                    ->label('Nombre de matchs à générer en avance')
                                    ->numeric()
                                    ->default(10)
                                    ->required(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Équipes virtuelles')
                            ->schema([
                                Forms\Components\Repeater::make('settings.virtual_config.teams')
                                    ->label('')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nom de l\'équipe')
                                            ->required(),

                                        Forms\Components\FileUpload::make('logo')
                                            ->label('Logo')
                                            ->image()
                                            ->directory('teams')
                                            ->maxSize(1024),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Ajouter une équipe')
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Section::make('Thème visuel')
                            ->schema([
                                Forms\Components\ColorPicker::make('settings.virtual_config.visual_theme.primary_color')
                                    ->label('Couleur principale')
                                    ->default('#00A859'),

                                Forms\Components\FileUpload::make('settings.virtual_config.visual_theme.stadium_images')
                                    ->label('Images de stades')
                                    ->image()
                                    ->directory('stadiums')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->maxSize(2048),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn (Forms\Get $get): bool => $get('is_virtual') === true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sport_category_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\IconColumn::make('is_live')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_virtual')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('match_duration')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListSports::route('/'),
            'create' => Pages\CreateSport::route('/create'),
            'edit' => Pages\EditSport::route('/{record}/edit'),
        ];
    }
}
