<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminNotificationResource\Pages;
use App\Models\AdminNotification;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdminNotificationResource extends Resource
{
    protected static ?string $model = AdminNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

    protected static ?string $pluralModelLabel = 'Notifications';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_sent', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la notification')
                    ->description('Contenu de votre notification')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->placeholder('Ex: Nouvelle promotion disponible'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('Rédigez le contenu de votre notification...')
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
                            ]),

                        Forms\Components\FileUpload::make('attachments')
                            ->label('Pièces jointes')
                            ->multiple()
                            ->directory('notifications')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxFiles(5)
                            ->maxSize(10240)
                            ->columnSpanFull()
                            ->helperText('Images ou PDF uniquement. Maximum 5 fichiers de 10 Mo chacun.')
                            ->downloadable()
                            ->previewable()
                            ->reorderable(),
                    ]),

                Forms\Components\Section::make('Destinataires')
                    ->description('À qui envoyer cette notification')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Forms\Components\Radio::make('recipient_type')
                            ->label('Type de destinataire')
                            ->options([
                                'all' => 'Tous les utilisateurs',
                                'specific' => 'Utilisateur spécifique',
                            ])
                            ->default('all')
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('user_id')
                            ->label('Sélectionner un utilisateur')
                            ->options(User::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(fn (callable $get) => $get('recipient_type') === 'specific')
                            ->visible(fn (callable $get) => $get('recipient_type') === 'specific')
                            ->columnSpanFull()
                            ->helperText('Choisissez l\'utilisateur qui recevra cette notification'),
                    ]),

                Forms\Components\Section::make('État')
                    ->schema([
                        Forms\Components\Placeholder::make('sent_status')
                            ->label('Statut d\'envoi')
                            ->content(fn (?AdminNotification $record) => $record?->is_sent
                                ? 'Envoyée le ' . $record->sent_at?->format('d/m/Y à H:i')
                                : 'Non envoyée')
                            ->hidden(fn (?AdminNotification $record) => $record === null),
                    ])
                    ->hidden(fn (?AdminNotification $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (AdminNotification $record) => \Illuminate\Support\Str::limit(strip_tags($record->content), 60)),

                Tables\Columns\TextColumn::make('recipient_type')
                    ->label('Destinataire')
                    ->badge()
                    ->formatStateUsing(fn (string $state, AdminNotification $record): string =>
                        $state === 'all'
                            ? 'Tous les utilisateurs'
                            : $record->user?->name ?? 'Utilisateur supprimé'
                    )
                    ->color(fn (string $state): string => match ($state) {
                        'all' => 'success',
                        'specific' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('attachments')
                    ->label('Pièces jointes')
                    ->formatStateUsing(fn (?array $state): string => $state ? count($state) . ' fichier(s)' : 'Aucune')
                    ->badge()
                    ->color(fn (?array $state): string => $state && count($state) > 0 ? 'warning' : 'gray'),

                Tables\Columns\IconColumn::make('is_sent')
                    ->label('Envoyée')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Envoyée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Non envoyée')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Envoyé par')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_sent')
                    ->label('Statut d\'envoi')
                    ->placeholder('Tous')
                    ->trueLabel('Envoyées')
                    ->falseLabel('Non envoyées'),

                Tables\Filters\SelectFilter::make('recipient_type')
                    ->label('Type de destinataire')
                    ->options([
                        'all' => 'Tous les utilisateurs',
                        'specific' => 'Utilisateur spécifique',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Envoyer')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (AdminNotification $record) => !$record->is_sent)
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer l\'envoi')
                    ->modalDescription('Êtes-vous sûr de vouloir envoyer cette notification ?')
                    ->modalSubmitActionLabel('Envoyer')
                    ->action(function (AdminNotification $record) {
                        $notification = new \App\Notifications\AdminNotificationSent($record);

                        if ($record->recipient_type === 'all') {
                            $users = User::where('is_active', true)->get();
                            \Illuminate\Support\Facades\Notification::send($users, $notification);
                        } else {
                            $record->user?->notify($notification);
                        }

                        $record->markAsSent();

                        \Filament\Notifications\Notification::make()
                            ->title('Notification envoyée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('Voir'),

                Tables\Actions\EditAction::make()
                    ->visible(fn (AdminNotification $record) => !$record->is_sent),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (AdminNotification $record) => !$record->is_sent),
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
            'index' => Pages\ListAdminNotifications::route('/'),
            'create' => Pages\CreateAdminNotification::route('/create'),
            'edit' => Pages\EditAdminNotification::route('/{record}/edit'),
        ];
    }
}
