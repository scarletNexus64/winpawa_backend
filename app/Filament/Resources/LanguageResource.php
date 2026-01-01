<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LanguageResource\Pages;
use App\Filament\Resources\LanguageResource\RelationManagers;
use App\Models\Language;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationLabel = 'Langues';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $modelLabel = 'Langue';

    protected static ?string $pluralModelLabel = 'Langues';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la langue')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Français, English, Español...'),

                        Forms\Components\TextInput::make('code')
                            ->label('Code ISO')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->placeholder('fr, en, es...')
                            ->helperText('Code ISO 639-1 (2 lettres)'),

                        Forms\Components\TextInput::make('locale')
                            ->label('Locale')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('fr_FR, en_US, es_ES...')
                            ->helperText('Format: langue_PAYS'),

                        Forms\Components\TextInput::make('flag_emoji')
                            ->label('Drapeau (Emoji)')
                            ->maxLength(10)
                            ->placeholder('🇫🇷, 🇬🇧, 🇪🇸...'),
                    ])->columns(2),

                Forms\Components\Section::make('Paramètres')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Langue active')
                            ->default(true)
                            ->helperText('Désactiver pour masquer cette langue'),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Langue par défaut')
                            ->default(false)
                            ->helperText('Une seule langue peut être définie par défaut'),

                        Forms\Components\Toggle::make('is_rtl')
                            ->label('De droite à gauche (RTL)')
                            ->default(false)
                            ->helperText('Pour les langues comme l\'arabe ou l\'hébreu'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Plus le nombre est petit, plus la langue apparaît en premier'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('flag_emoji')
                    ->label('')
                    ->sortable(false),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('locale')
                    ->label('Locale')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Par défaut')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_rtl')
                    ->label('RTL')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Toutes')
                    ->trueLabel('Actives')
                    ->falseLabel('Inactives'),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Par défaut')
                    ->placeholder('Toutes')
                    ->trueLabel('Langue par défaut')
                    ->falseLabel('Langues secondaires'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Language $record): bool => !$record->is_default),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
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
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}
