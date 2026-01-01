<?php

namespace App\Filament\Pages;

use App\Enums\GameType;
use App\Models\Game;
use App\Models\GameModule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ModuleGames extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string $view = 'filament.pages.module-games';

    protected static bool $shouldRegisterNavigation = false;

    public ?string $module = null;

    public ?GameModule $gameModule = null;

    public function mount(?string $module = null): void
    {
        $this->module = $module;
        $this->gameModule = GameModule::where('slug', $module)->firstOrFail();

        // Rediriger si le module est verrouillé
        if ($this->gameModule->is_locked) {
            redirect()->route('filament.admin.pages.dashboard');
        }
    }

    public function getTitle(): string | Htmlable
    {
        return $this->gameModule?->name ?? 'Module';
    }

    public function getHeading(): string | Htmlable
    {
        return $this->gameModule?->name ?? 'Module';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return $this->gameModule?->description;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Game::query()
                    ->where('module_id', $this->gameModule->id)
            )
            ->columns([
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
                Tables\Actions\EditAction::make()
                    ->form($this->getGameForm()),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Game $record) => $record->is_active ? 'Désactiver' : 'Activer')
                    ->icon(fn (Game $record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (Game $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Game $record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nouveau jeu')
                    ->form($this->getGameForm())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['module_id'] = $this->gameModule->id;
                        return $data;
                    }),
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

    protected function getGameForm(): array
    {
        return [
            Forms\Components\Section::make('Informations du jeu')
                ->schema([
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
                ])->columns(3),

            Forms\Components\Section::make('Configuration RNG')
                ->schema([
                    Forms\Components\TextInput::make('rtp')
                        ->label('RTP (%)')
                        ->numeric()
                        ->minValue(50)
                        ->maxValue(99)
                        ->suffix('%')
                        ->default(75),

                    Forms\Components\TextInput::make('win_frequency')
                        ->label('Fréquence de gains (%)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(99)
                        ->suffix('%')
                        ->default(35),

                    Forms\Components\TagsInput::make('multipliers')
                        ->label('Multiplicateurs')
                        ->placeholder('Ajouter un multiplicateur')
                        ->default([2, 5, 10]),
                ])->columns(3),

            Forms\Components\Section::make('Affichage')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Actif')
                        ->default(true),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Mis en avant')
                        ->default(false),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0),
                ])->columns(3),
        ];
    }
}
