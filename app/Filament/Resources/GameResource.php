<?php

namespace App\Filament\Resources;

use App\Enums\GameType;
use App\Filament\Resources\GameResource\Pages;
use App\Models\Game;
use App\Models\GameCategory;
use App\Models\GameModule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Gestion des Jeux';

    protected static ?string $navigationLabel = 'Jeux Casino';

    protected static ?string $modelLabel = 'Jeu';

    protected static ?string $pluralModelLabel = 'Jeux';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $casinoModule = GameModule::where('slug', 'jeux-casino')->first();
        if (!$casinoModule) {
            return null;
        }
        return static::getModel()::where('module_id', $casinoModule->id)->where('is_active', true)->count();
    }

    public static function getEloquentQuery(): Builder
    {
        $casinoModule = GameModule::where('slug', 'jeux-casino')->first();

        return parent::getEloquentQuery()->when($casinoModule, function ($query) use ($casinoModule) {
            $query->where('module_id', $casinoModule->id);
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du jeu')
                    ->description('Configuration de base du jeu')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Select::make('module_id')
                            ->label('Module')
                            ->options(GameModule::active()->ordered()->pluck('name', 'id'))
                            ->default(fn () => GameModule::where('slug', 'jeux-casino')->first()?->id)
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie')
                            ->options(GameCategory::active()->ordered()->pluck('name', 'id'))
                            ->required()
                            ->native(false)
                            ->helperText('Sélectionnez la catégorie du jeu'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nom du jeu')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Type de jeu')
                            ->options(collect(GameType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()]))
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(4),

                Forms\Components\Section::make('Image du jeu')
                    ->description('Téléchargez ou sélectionnez une image pour ce jeu')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\ViewField::make('current_image')
                            ->label('Image actuelle')
                            ->view('filament.forms.components.game-image-preview')
                            ->visible(fn ($record) => $record && $record->image)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('new_image')
                            ->label('Télécharger une nouvelle image (laissez vide pour garder l\'actuelle)')
                            ->image()
                            ->disk('game_images')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('800')
                            ->maxSize(2048)
                            ->helperText('Format recommandé : 800x800px (max 2MB). Formats acceptés : PNG, JPG, WEBP')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('image', $state);
                                }
                            })
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuration RNG')
                    ->description('Paramètres de rentabilité et de gains')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\TextInput::make('rtp')
                            ->label('RTP (%)')
                            ->helperText('Return to Player - Pourcentage de retour aux joueurs')
                            ->numeric()
                            ->minValue(50)
                            ->maxValue(99)
                            ->suffix('%')
                            ->default(75),

                        Forms\Components\TextInput::make('win_frequency')
                            ->label('Fréquence de gains (%)')
                            ->helperText('Pourcentage de parties gagnantes (génère automatiquement les segments perdants pour Apple of Fortune)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(99)
                            ->suffix('%')
                            ->default(35)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Auto-update info text
                            }),

                        Forms\Components\TagsInput::make('multipliers')
                            ->label('Multiplicateurs gagnants')
                            ->helperText('Ex: 2, 5, 10 (pour Apple of Fortune, des segments perdants seront ajoutés automatiquement)')
                            ->placeholder('Ajouter un multiplicateur')
                            ->splitKeys(['Tab', ',', ' '])
                            ->default([2, 5, 10]),
                    ])->columns(3),

                Forms\Components\Section::make('Configuration Apple of Fortune (Roulette)')
                    ->description('Visualisation de la configuration générée automatiquement')
                    ->icon('heroicon-o-circle-stack')
                    ->schema([
                        Forms\Components\Placeholder::make('roulette_config')
                            ->label('Configuration des segments')
                            ->content(function ($record) {
                                if (!$record || !isset($record->settings['prizes'])) {
                                    return '⚠️ Aucune configuration générée. Sauvegardez le jeu pour générer les segments.';
                                }

                                $prizes = $record->settings['prizes'] ?? [];
                                $winningSegments = $record->settings['winning_segments'] ?? 0;
                                $totalSegments = $record->settings['segments'] ?? 0;
                                $losingSegments = $totalSegments - $winningSegments;
                                $winRate = $totalSegments > 0 ? round(($winningSegments / $totalSegments) * 100) : 0;

                                $html = '<div class="space-y-3">';
                                $html .= '<div class="grid grid-cols-3 gap-2 text-sm">';
                                $html .= '<div class="bg-green-50 dark:bg-green-900/20 p-2 rounded border border-green-200 dark:border-green-800">';
                                $html .= '<div class="font-semibold text-green-600 dark:text-green-400">✅ Segments gagnants</div>';
                                $html .= '<div class="text-2xl font-bold text-green-700 dark:text-green-300">' . $winningSegments . '</div>';
                                $html .= '</div>';
                                $html .= '<div class="bg-red-50 dark:bg-red-900/20 p-2 rounded border border-red-200 dark:border-red-800">';
                                $html .= '<div class="font-semibold text-red-600 dark:text-red-400">❌ Segments perdants</div>';
                                $html .= '<div class="text-2xl font-bold text-red-700 dark:text-red-300">' . $losingSegments . '</div>';
                                $html .= '</div>';
                                $html .= '<div class="bg-blue-50 dark:bg-blue-900/20 p-2 rounded border border-blue-200 dark:border-blue-800">';
                                $html .= '<div class="font-semibold text-blue-600 dark:text-blue-400">📊 Probabilité</div>';
                                $html .= '<div class="text-2xl font-bold text-blue-700 dark:text-blue-300">' . $winRate . '%</div>';
                                $html .= '</div>';
                                $html .= '</div>';

                                $html .= '<div class="mt-4"><div class="font-semibold mb-2 text-gray-700 dark:text-gray-300">🎰 Détail des segments :</div>';
                                $html .= '<div class="grid grid-cols-2 md:grid-cols-3 gap-2">';

                                foreach ($prizes as $segmentNumber => $prize) {
                                    $multiplier = $prize['multiplier'] ?? 0;
                                    $color = $prize['color'] ?? '#ccc';
                                    $isWinner = $multiplier > 0;

                                    $borderColor = $isWinner ? 'border-green-500' : 'border-red-500';
                                    $bgColor = $isWinner ? 'bg-white dark:bg-gray-800' : 'bg-gray-100 dark:bg-gray-900';

                                    $html .= '<div class="flex items-center gap-2 p-2 rounded border ' . $borderColor . ' ' . $bgColor . '">';
                                    $html .= '<div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background-color: ' . $color . '"></div>';
                                    $html .= '<div class="flex-1">';
                                    $html .= '<div class="text-xs text-gray-500 dark:text-gray-400">Segment #' . $segmentNumber . '</div>';
                                    $html .= '<div class="font-bold ' . ($isWinner ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400') . '">';
                                    $html .= $isWinner ? $multiplier . 'x' : '💀 0x';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                }

                                $html .= '</div></div>';
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record && $record->type === \App\Enums\GameType::ROULETTE)
                    ->collapsible(),

                Forms\Components\Section::make('Limites de mise')
                    ->description('Montants minimum et maximum autorisés')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\TextInput::make('min_bet')
                            ->label('Mise minimum')
                            ->numeric()
                            ->prefix('FCFA')
                            ->default(100),

                        Forms\Components\TextInput::make('max_bet')
                            ->label('Mise maximum')
                            ->numeric()
                            ->prefix('FCFA')
                            ->default(100000),
                    ])->columns(2),

                Forms\Components\Section::make('Affichage')
                    ->description('Options d\'affichage')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Mis en avant')
                            ->default(false),

                        Forms\Components\Toggle::make('is_configured')
                            ->label('Configuré')
                            ->helperText('Marquer ce jeu comme déjà configuré')
                            ->default(false),
                    ])->columns(4),

                Forms\Components\Section::make('Maintenance')
                    ->description('Mettre ce jeu en mode maintenance')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Forms\Components\Toggle::make('is_maintenance')
                            ->label('Mode maintenance')
                            ->helperText('Activer pour rendre ce jeu indisponible temporairement')
                            ->live()
                            ->default(false),

                        Forms\Components\Textarea::make('maintenance_message')
                            ->label('Message de maintenance')
                            ->helperText('Message affiché aux joueurs pendant la maintenance')
                            ->rows(3)
                            ->visible(fn (Forms\Get $get) => $get('is_maintenance'))
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->getStateUsing(function ($record) {
                        return $record->image ? url('images/' . $record->image) : url('/images/logo.png');
                    })
                    ->size(60),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Game $record) => $record->type?->icon() . ' ' . $record->type?->label())
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn (GameType $state): string => $state->category())
                    ->color(fn (GameType $state): string => $state->categoryColor())
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rtp')
                    ->label('RTP')
                    ->suffix('%')
                    ->sortable()
                    ->color('info')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('win_frequency')
                    ->label('Fréq. Gains')
                    ->suffix('%')
                    ->sortable()
                    ->color('success')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('actual_rtp')
                    ->label('RTP Réel')
                    ->suffix('%')
                    ->getStateUsing(fn (Game $record) => number_format($record->actual_rtp, 1))
                    ->color(fn (Game $record) => $record->actual_rtp > $record->rtp ? 'danger' : 'success')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('min_bet')
                    ->label('Mise Min')
                    ->money('XAF')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('max_bet')
                    ->label('Mise Max')
                    ->money('XAF')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bets_count')
                    ->label('Total Paris')
                    ->counts('bets')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Vedette')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_maintenance')
                    ->label('Maintenance')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Mis en avant'),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(GameType::getCategoriesOptions())
                    ->query(function ($query, $state) {
                        if (filled($state['value'])) {
                            $types = collect(GameType::cases())
                                ->filter(fn ($type) => $type->category() === $state['value'])
                                ->map(fn ($type) => $type->value)
                                ->toArray();
                            return $query->whereIn('type', $types);
                        }
                    }),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type de jeu')
                    ->options(collect(GameType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Game $record) => $record->is_active ? 'Désactiver' : 'Activer')
                    ->icon(fn (Game $record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (Game $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Game $record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('type')
                    ->label('Catégorie')
                    ->getTitleFromRecordUsing(fn (Game $record): string => $record->type?->category() ?? 'Non catégorisé')
                    ->collapsible(),
            ])
            ->defaultGroup('type')
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}
