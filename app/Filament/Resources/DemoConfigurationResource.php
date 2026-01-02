<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DemoConfigurationResource\Pages;
use App\Models\DemoConfiguration;
use App\Models\Game;
use App\Services\DemoSimulationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DemoConfigurationResource extends Resource
{
    protected static ?string $model = DemoConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Mode Demo';

    protected static ?string $modelLabel = 'Configuration Demo';

    protected static ?string $pluralModelLabel = 'Configurations Demo';

    protected static ?string $navigationGroup = 'Demo';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('name')
                            ->label('Configuration Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activate this configuration')
                            ->default(true)
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Period & Games')
                    ->schema([
                        Forms\Components\Select::make('period_type')
                            ->label('Period Type')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                            ])
                            ->default('daily')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date (optional)')
                            ->after('start_date')
                            ->columnSpan(1),
                        Forms\Components\CheckboxList::make('selected_games')
                            ->label('Selected Games')
                            ->options(Game::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->bulkToggleable()
                            ->gridDirection('row')
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Bet Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('daily_bet_count')
                            ->label('Bets per day')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1000)
                            ->default(10)
                            ->suffix('bets/day'),
                        Forms\Components\TextInput::make('win_rate')
                            ->label('Win Rate (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(45)
                            ->suffix('%'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Bet Amounts')
                    ->schema([
                        Forms\Components\TextInput::make('min_bet')
                            ->label('Minimum Bet')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(100)
                            ->prefix('FCFA'),
                        Forms\Components\TextInput::make('max_bet')
                            ->label('Maximum Bet')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(10000)
                            ->prefix('FCFA'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Win Multipliers')
                    ->schema([
                        Forms\Components\TextInput::make('min_win_multiplier')
                            ->label('Minimum Multiplier')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1.5)
                            ->suffix('x')
                            ->step(0.1),
                        Forms\Components\TextInput::make('max_win_multiplier')
                            ->label('Maximum Multiplier')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(10)
                            ->suffix('x')
                            ->step(0.1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_type')
                    ->label('Period')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'daily' => 'success',
                        'weekly' => 'warning',
                        'monthly' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('win_rate')
                    ->label('Win Rate')
                    ->suffix('%')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('daily_bet_count')
                    ->label('Bets/day')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('simulatedData_count')
                    ->label('Generated Data')
                    ->counts('simulatedData')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('generate')
                    ->label('Generate')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (DemoConfiguration $record) {
                        try {
                            $service = app(DemoSimulationService::class);
                            $data = $service->generateData($record);

                            Notification::make()
                                ->title('Data generated successfully')
                                ->body(count($data) . ' days of data generated.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Generation error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('clear')
                    ->label('Clear')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (DemoConfiguration $record) {
                        try {
                            $service = app(DemoSimulationService::class);
                            $deleted = $service->clearData($record);

                            Notification::make()
                                ->title('Data cleared')
                                ->body($deleted . ' records deleted.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Deletion error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDemoConfigurations::route('/'),
            'create' => Pages\CreateDemoConfiguration::route('/create'),
            'view' => Pages\ViewDemoConfiguration::route('/{record}'),
            'edit' => Pages\EditDemoConfiguration::route('/{record}/edit'),
        ];
    }
}
