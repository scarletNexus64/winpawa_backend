<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegalPageResource\Pages;
use App\Models\LegalPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LegalPageResource extends Resource
{
    protected static ?string $model = LegalPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Juridique';

    protected static ?string $navigationLabel = 'Pages Légales';

    protected static ?string $modelLabel = 'Page Légale';

    protected static ?string $pluralModelLabel = 'Pages Légales';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la page')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type de page')
                            ->options([
                                'privacy' => 'Politique de Confidentialité',
                                'terms' => 'Conditions Générales d\'Utilisation (CGU)',
                                'cookies' => 'Politique des Cookies',
                                'data_protection' => 'Protection des Données',
                            ])
                            ->required()
                            ->native(false)
                            ->disabled(fn (?LegalPage $record) => $record !== null),

                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Page active')
                            ->default(true)
                            ->helperText('Désactiver pour masquer cette page'),
                    ]),

                Forms\Components\Section::make('Contenu')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu de la page')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'blockquote',
                                'codeBlock',
                            ]),
                    ]),

                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Placeholder::make('last_updated_at')
                            ->label('Dernière mise à jour')
                            ->content(fn (?LegalPage $record) => $record?->last_updated_at?->format('d/m/Y à H:i') ?? 'Jamais')
                            ->hidden(fn (?LegalPage $record) => $record === null),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('Créée le')
                            ->content(fn (?LegalPage $record) => $record?->created_at?->format('d/m/Y à H:i') ?? '-')
                            ->hidden(fn (?LegalPage $record) => $record === null),
                    ])
                    ->columns(2)
                    ->hidden(fn (?LegalPage $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'privacy' => 'Confidentialité',
                        'terms' => 'CGU',
                        'cookies' => 'Cookies',
                        'data_protection' => 'Protection Données',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'privacy' => 'info',
                        'terms' => 'success',
                        'cookies' => 'warning',
                        'data_protection' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_updated_at')
                    ->label('Dernière MAJ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

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

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'privacy' => 'Confidentialité',
                        'terms' => 'CGU',
                        'cookies' => 'Cookies',
                        'data_protection' => 'Protection Données',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('type');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegalPages::route('/'),
            'create' => Pages\CreateLegalPage::route('/create'),
            'edit' => Pages\EditLegalPage::route('/{record}/edit'),
        ];
    }
}
