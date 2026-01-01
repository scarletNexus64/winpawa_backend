<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Bannières Publicitaires';

    protected static ?string $modelLabel = 'Bannière';

    protected static ?string $pluralModelLabel = 'Bannières';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la bannière')
                    ->description('Détails et contenu de la bannière publicitaire')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->directory('banners')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('link')
                            ->label('Lien (URL)')
                            ->url()
                            ->placeholder('https://example.com')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Positionnement & Affichage')
                    ->description('Où et comment afficher la bannière')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Select::make('position')
                            ->label('Position')
                            ->options([
                                'home' => 'Page d\'accueil',
                                'sidebar' => 'Barre latérale',
                                'games' => 'Page des jeux',
                                'footer' => 'Pied de page',
                                'popup' => 'Popup',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->helperText('Plus petit = affiché en premier'),

                        Forms\Components\Toggle::make('open_in_new_tab')
                            ->label('Ouvrir dans un nouvel onglet')
                            ->default(true),
                    ])->columns(3),

                Forms\Components\Section::make('Période d\'affichage')
                    ->description('Dates de début et fin (optionnel)')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Date de début')
                            ->helperText('Laisser vide pour démarrer immédiatement'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Date de fin')
                            ->helperText('Laisser vide pour pas de limite'),
                    ])->columns(2),

                Forms\Components\Section::make('Statut')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activer la bannière')
                            ->default(true)
                            ->helperText('Désactiver pour mettre en pause sans supprimer'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Aperçu')
                    ->size(80)
                    ->square(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Banner $record) => $record->description),

                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'home' => 'success',
                        'sidebar' => 'info',
                        'games' => 'warning',
                        'footer' => 'gray',
                        'popup' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'home' => 'Accueil',
                        'sidebar' => 'Sidebar',
                        'games' => 'Jeux',
                        'footer' => 'Footer',
                        'popup' => 'Popup',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('impressions')
                    ->label('Vues')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('clicks')
                    ->label('Clics')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('ctr')
                    ->label('CTR')
                    ->suffix('%')
                    ->badge()
                    ->color('info')
                    ->tooltip('Taux de clics (Click Through Rate)'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),

                Tables\Filters\SelectFilter::make('position')
                    ->label('Position')
                    ->options([
                        'home' => 'Accueil',
                        'sidebar' => 'Sidebar',
                        'games' => 'Jeux',
                        'footer' => 'Footer',
                        'popup' => 'Popup',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Aperçu')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn (Banner $record) => view('filament.resources.banner.preview', ['record' => $record]))
                    ->modalSubmitAction(false),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Banner $record) => $record->is_active ? 'Désactiver' : 'Activer')
                    ->icon(fn (Banner $record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (Banner $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Banner $record) => $record->update(['is_active' => !$record->is_active])),

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
