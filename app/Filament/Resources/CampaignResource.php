<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Campagnes';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la campagne')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('channel')
                            ->label('Canal d\'envoi')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'sms' => 'SMS',
                                'email' => 'Email',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('whatsapp_template', null)),
                    ]),

                Forms\Components\Section::make('Contenu du message')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Sujet')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => $get('channel') === 'email')
                            ->required(fn (Forms\Get $get): bool => $get('channel') === 'email'),

                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('whatsapp_template')
                            ->label('Template WhatsApp')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => $get('channel') === 'whatsapp')
                            ->helperText('Nom du template approuvé dans WhatsApp Business'),

                        Forms\Components\FileUpload::make('attachments')
                            ->label('Pièces jointes')
                            ->multiple()
                            ->directory('campaign-attachments')
                            ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxFiles(10)
                            ->maxSize(10240)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Destinataires')
                    ->schema([
                        Forms\Components\Radio::make('recipient_type')
                            ->label('Type de destinataires')
                            ->options([
                                'all' => 'Tous les utilisateurs',
                                'specific' => 'Utilisateurs spécifiques',
                            ])
                            ->default('all')
                            ->required()
                            ->live()
                            ->inline(),

                        Forms\Components\Select::make('specific_users')
                            ->label('Sélectionner les utilisateurs')
                            ->multiple()
                            ->searchable()
                            ->options(User::all()->pluck('name', 'id'))
                            ->visible(fn (Forms\Get $get): bool => $get('recipient_type') === 'specific')
                            ->required(fn (Forms\Get $get): bool => $get('recipient_type') === 'specific')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Programmation')
                    ->schema([
                        Forms\Components\Radio::make('schedule_type')
                            ->label('Type de programmation')
                            ->options([
                                'immediate' => 'Envoi immédiat',
                                'scheduled' => 'Programmé (une fois)',
                                'recurring' => 'Récurrent',
                            ])
                            ->default('immediate')
                            ->required()
                            ->live()
                            ->inline(),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Date et heure d\'envoi')
                            ->visible(fn (Forms\Get $get): bool => $get('schedule_type') === 'scheduled')
                            ->required(fn (Forms\Get $get): bool => $get('schedule_type') === 'scheduled')
                            ->minDate(now())
                            ->seconds(false),

                        Forms\Components\Select::make('recurrence_pattern')
                            ->label('Fréquence')
                            ->options([
                                'daily' => 'Quotidien',
                                'weekly' => 'Hebdomadaire',
                                'monthly' => 'Mensuel',
                                'custom' => 'Personnalisé',
                            ])
                            ->visible(fn (Forms\Get $get): bool => $get('schedule_type') === 'recurring')
                            ->required(fn (Forms\Get $get): bool => $get('schedule_type') === 'recurring')
                            ->live(),

                        Forms\Components\KeyValue::make('recurrence_config')
                            ->label('Configuration récurrence')
                            ->visible(fn (Forms\Get $get): bool => $get('schedule_type') === 'recurring')
                            ->helperText('Ex: day => thursday, time => 10:00')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('recurrence_start')
                            ->label('Début de récurrence')
                            ->visible(fn (Forms\Get $get): bool => $get('schedule_type') === 'recurring')
                            ->required(fn (Forms\Get $get): bool => $get('schedule_type') === 'recurring')
                            ->minDate(now())
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('recurrence_end')
                            ->label('Fin de récurrence')
                            ->visible(fn (Forms\Get $get): bool => $get('schedule_type') === 'recurring')
                            ->minDate(fn (Forms\Get $get) => $get('recurrence_start'))
                            ->seconds(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'whatsapp' => 'success',
                        'sms' => 'info',
                        'email' => 'warning',
                    })
                    ->formatStateUsing(fn (Campaign $record) => $record->channel_label),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'info',
                        'sending' => 'warning',
                        'sent' => 'success',
                        'paused' => 'danger',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (Campaign $record) => $record->status_label),

                Tables\Columns\TextColumn::make('total_recipients')
                    ->label('Destinataires')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('sent_count')
                    ->label('Envoyés')
                    ->sortable()
                    ->alignCenter()
                    ->color('success'),

                Tables\Columns\TextColumn::make('failed_count')
                    ->label('Échecs')
                    ->sortable()
                    ->alignCenter()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('schedule_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'immediate' => 'Immédiat',
                        'scheduled' => 'Programmé',
                        'recurring' => 'Récurrent',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Programmé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Envoyé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->label('Canal')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'sms' => 'SMS',
                        'email' => 'Email',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'scheduled' => 'Programmée',
                        'sending' => 'Envoi en cours',
                        'sent' => 'Envoyée',
                        'paused' => 'En pause',
                        'cancelled' => 'Annulée',
                    ]),

                Tables\Filters\SelectFilter::make('schedule_type')
                    ->label('Type')
                    ->options([
                        'immediate' => 'Immédiat',
                        'scheduled' => 'Programmé',
                        'recurring' => 'Récurrent',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Envoyer')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['draft', 'scheduled']))
                    ->action(function (Campaign $record) {
                        if ($record->schedule_type === 'immediate') {
                            SendCampaignJob::dispatch($record);
                            Notification::make()
                                ->success()
                                ->title('Campagne en cours d\'envoi')
                                ->body('La campagne sera envoyée en arrière-plan')
                                ->send();
                        } else {
                            $record->update(['status' => 'scheduled']);
                            Notification::make()
                                ->success()
                                ->title('Campagne programmée')
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('pause')
                    ->label('Mettre en pause')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Campaign $record): bool => $record->status === 'sending')
                    ->action(function (Campaign $record) {
                        $record->update(['status' => 'paused']);
                        Notification::make()
                            ->success()
                            ->title('Campagne mise en pause')
                            ->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['scheduled', 'paused']))
                    ->action(function (Campaign $record) {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()
                            ->success()
                            ->title('Campagne annulée')
                            ->send();
                    }),

                Tables\Actions\Action::make('duplicate')
                    ->label('Dupliquer')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Campaign $record) {
                        $newCampaign = $record->replicate();
                        $newCampaign->name = $record->name . ' (copie)';
                        $newCampaign->status = 'draft';
                        $newCampaign->sent_count = 0;
                        $newCampaign->failed_count = 0;
                        $newCampaign->sent_at = null;
                        $newCampaign->created_by = auth()->id();
                        $newCampaign->save();

                        Notification::make()
                            ->success()
                            ->title('Campagne dupliquée')
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['draft', 'scheduled'])),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['draft', 'cancelled', 'failed'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
