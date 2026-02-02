<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VirtualMatchResource\Pages;
use App\Filament\Resources\VirtualMatchResource\RelationManagers;
use App\Models\VirtualMatch;
use App\Enums\VirtualMatchStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;

class VirtualMatchResource extends Resource
{
    protected static ?string $model = VirtualMatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationLabel = 'Virtual Matches';

    protected static ?string $navigationGroup = 'Jeux Virtuels';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ==================== INFORMATIONS GÉNÉRALES ====================
                Forms\Components\Section::make('Informations Générales')
                    ->description('Identification et statut du match virtuel')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->default(fn () => VirtualMatch::generateReference())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->helperText('Généré automatiquement'),

                        Forms\Components\Select::make('sport_type')
                            ->label('Type de sport')
                            ->options([
                                'football' => 'Football ⚽',
                                'basketball' => 'Basketball 🏀',
                                'tennis' => 'Tennis 🎾',
                            ])
                            ->default('football')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('league', null)),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'upcoming' => 'À venir',
                                'live' => 'En direct',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                            ])
                            ->default('upcoming')
                            ->required()
                            ->reactive(),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // ==================== COMPÉTITION ====================
                Forms\Components\Section::make('Compétition')
                    ->description('Ligue, saison et durée du match')
                    ->schema([
                        Forms\Components\Select::make('league')
                            ->label('Ligue / Compétition')
                            ->options(function (Forms\Get $get) {
                                $sportType = $get('sport_type') ?? 'football';

                                return match ($sportType) {
                                    'football' => [
                                        'Premier League' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿 Premier League (Angleterre)',
                                        'La Liga' => '🇪🇸 La Liga (Espagne)',
                                        'Serie A' => '🇮🇹 Serie A (Italie)',
                                        'Bundesliga' => '🇩🇪 Bundesliga (Allemagne)',
                                        'Ligue 1' => '🇫🇷 Ligue 1 (France)',
                                        'Champions League' => '⭐ UEFA Champions League',
                                        'Europa League' => '🏆 UEFA Europa League',
                                        'Virtual World Cup' => '🌍 Coupe du Monde Virtuelle',
                                        'Virtual Nations Cup' => '🏅 Coupe des Nations Virtuelle',
                                    ],
                                    'basketball' => [
                                        'NBA Virtual League' => '🏀 NBA Virtual League',
                                        'Euroleague Virtual' => '🇪🇺 Euroleague Virtuelle',
                                        'Virtual Basketball Championship' => '🏆 Championnat Virtuel',
                                    ],
                                    'tennis' => [
                                        'Virtual Grand Slam' => '🎾 Grand Slam Virtuel',
                                        'Virtual Masters Series' => '🏆 Masters Series Virtuel',
                                        'Virtual ATP Tour' => '🌟 ATP Tour Virtuel',
                                    ],
                                    default => [
                                        'League Mode' => 'League Mode',
                                    ],
                                };
                            })
                            ->searchable()
                            ->placeholder('Sélectionnez une ligue')
                            ->required(),

                        Forms\Components\TextInput::make('season')
                            ->label('Saison')
                            ->placeholder('Ex: 2024/2025')
                            ->default('2024/2025'),

                        Forms\Components\Select::make('duration')
                            ->label('Durée du match')
                            ->options([
                                1 => '1 minute (Rapide)',
                                3 => '3 minutes (Standard)',
                                5 => '5 minutes (Long)',
                            ])
                            ->default(3)
                            ->required()
                            ->helperText('Durée de simulation du match'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // ==================== AFFRONTEMENT ====================
                Forms\Components\Section::make('Affrontement')
                    ->description('Équipes et logos')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Fieldset::make('Équipe Domicile')
                                    ->schema([
                                        Forms\Components\TextInput::make('team_home')
                                            ->label('Nom de l\'équipe')
                                            ->required()
                                            ->placeholder('Ex: Lions FC'),

                                        Forms\Components\FileUpload::make('team_home_logo')
                                            ->label('Logo')
                                            ->image()
                                            ->disk('game_images')
                                            ->directory('logo_equipe')
                                            ->visibility('public')
                                            ->maxSize(2048)
                                            ->imagePreviewHeight('100')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios(['1:1'])
                                            ->helperText('Uploadez un logo (max 2MB)'),
                                    ]),

                                Forms\Components\Fieldset::make('Équipe Extérieur')
                                    ->schema([
                                        Forms\Components\TextInput::make('team_away')
                                            ->label('Nom de l\'équipe')
                                            ->required()
                                            ->placeholder('Ex: Eagles United'),

                                        Forms\Components\FileUpload::make('team_away_logo')
                                            ->label('Logo')
                                            ->image()
                                            ->disk('game_images')
                                            ->directory('logo_equipe')
                                            ->visibility('public')
                                            ->maxSize(2048)
                                            ->imagePreviewHeight('100')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios(['1:1'])
                                            ->helperText('Uploadez un logo (max 2MB)'),
                                    ]),
                            ]),
                    ])
                    ->collapsible(),

                // ==================== PROGRAMMATION & TIMING ====================
                Forms\Components\Section::make('Programmation & Timing')
                    ->description('Planification et paramètres temporels')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Date et heure de début')
                            ->required()
                            ->seconds(false)
                            ->default(fn (Forms\Get $get) => $get('status') === 'live' ? now() : now()->addMinutes(5))
                            ->disabled(fn (Forms\Get $get) => $get('status') === 'live')
                            ->dehydrated()
                            ->helperText(fn (Forms\Get $get) => $get('status') === 'live'
                                ? '⚡ Match en direct : la date est définie automatiquement à maintenant'
                                : 'Choisissez quand le match débutera'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Date et heure de fin')
                            ->seconds(false)
                            ->helperText('Sera calculé automatiquement si non renseigné'),

                        Forms\Components\TextInput::make('bet_closure_seconds')
                            ->label('Fermeture des paris (secondes avant)')
                            ->numeric()
                            ->default(5)
                            ->minValue(0)
                            ->maxValue(300)
                            ->suffix('secondes')
                            ->helperText('Les paris seront fermés X secondes avant le début')
                            ->required(),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // ==================== CONFIGURATION DES PARIS ====================
                Forms\Components\Section::make('Configuration des Paris')
                    ->description('Définir les limites de mise et activer les types de paris que les utilisateurs pourront placer')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('min_bet_amount')
                                    ->label('Mise minimum')
                                    ->numeric()
                                    ->default(100)
                                    ->minValue(1)
                                    ->suffix('FCFA')
                                    ->required()
                                    ->helperText('Montant minimum qu\'un utilisateur peut parier'),

                                Forms\Components\TextInput::make('max_bet_amount')
                                    ->label('Mise maximum')
                                    ->numeric()
                                    ->default(100000)
                                    ->minValue(1)
                                    ->suffix('FCFA')
                                    ->required()
                                    ->helperText('Montant maximum qu\'un utilisateur peut parier'),
                            ]),

                        Forms\Components\CheckboxList::make('available_markets')
                            ->label('Types de paris autorisés (marchés)')
                            ->options(function (Forms\Get $get) {
                                $sportType = $get('sport_type') ?? 'football';

                                return match ($sportType) {
                                    'football' => [
                                        'result' => '🎯 Résultat du match (1X2) - Qui va gagner ?',
                                        'double_chance' => '🔀 Double Chance - Couvrir 2 résultats',
                                        'both_teams_score' => '⚽ Les deux équipes marquent',
                                        'over_under' => '📊 Total de buts (Over/Under 1.5, 2.5, etc.)',
                                        'exact_score' => '🎲 Score exact',
                                        'first_half' => '⏱️ Résultat à la mi-temps',
                                        'handicap' => '⚖️ Handicap',
                                    ],
                                    'basketball' => [
                                        'result' => '🎯 Vainqueur du match',
                                        'over_under' => '📊 Total de points (Over/Under)',
                                        'handicap' => '⚖️ Handicap de points',
                                        'first_quarter' => '⏱️ Résultat 1er quart-temps',
                                    ],
                                    'tennis' => [
                                        'result' => '🎯 Vainqueur du match',
                                        'exact_sets' => '🎲 Score en sets',
                                        'over_under' => '📊 Total de jeux',
                                        'first_set' => '⏱️ Vainqueur 1er set',
                                    ],
                                    default => [],
                                };
                            })
                            ->default(['result', 'both_teams_score', 'over_under'])
                            ->columns(1)
                            ->columnSpanFull()
                            ->helperText('Cochez les types de paris que les utilisateurs pourront placer sur ce match'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                // ==================== CONFIGURATION DES COTES ====================
                Forms\Components\Section::make('Configuration des Cotes')
                    ->description('Personnaliser les cotes pour chaque type de pari (laisser vide pour utiliser les cotes par défaut)')
                    ->schema([
                        Forms\Components\Placeholder::make('odds_info')
                            ->label('')
                            ->content('⚙️ Configurez les cotes pour chaque marché. Si vous ne remplissez rien, les cotes par défaut seront utilisées.')
                            ->columnSpanFull(),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_all_odds')
                                ->label('🎲 Générer toutes les cotes automatiquement')
                                ->color('success')
                                ->outlined()
                                ->requiresConfirmation()
                                ->modalHeading('Générer toutes les cotes ?')
                                ->modalDescription('Cette action va pré-remplir tous les marchés avec les cotes par défaut. Les cotes déjà configurées seront écrasées.')
                                ->modalSubmitActionLabel('Oui, générer')
                                ->action(function (Forms\Set $set) {
                                    $defaultOdds = VirtualMatch::getDefaultOdds();

                                    // Convertir au format Repeater pour chaque marché
                                    $totalMarkets = 0;
                                    $totalOptions = 0;

                                    foreach ($defaultOdds as $market => $options) {
                                        $repeaterData = [];

                                        foreach ($options as $key => $data) {
                                            $repeaterData[] = [
                                                'key' => $key,
                                                'label' => $data['label'] ?? '',
                                                'description' => $data['description'] ?? '',
                                                'odd' => $data['odd'] ?? 1.0,
                                            ];
                                            $totalOptions++;
                                        }

                                        $set("odds.{$market}", $repeaterData);
                                        $totalMarkets++;
                                    }

                                    Notification::make()
                                        ->success()
                                        ->title('Cotes générées avec succès')
                                        ->body("{$totalMarkets} marchés générés avec {$totalOptions} options de paris au total.")
                                        ->send();
                                })
                        ])
                            ->columnSpanFull(),

                        Forms\Components\Tabs::make('Cotes')
                            ->tabs([
                                // 🎯 Résultat (1X2)
                                Forms\Components\Tabs\Tab::make('Résultat (1X2)')
                                    ->icon('heroicon-o-trophy')
                                    ->schema([
                                        Forms\Components\Repeater::make('odds.result')
                                            ->label('Cotes pour le résultat du match')
                                            ->schema([
                                                Forms\Components\Select::make('key')
                                                    ->label('Option')
                                                    ->options([
                                                        'home_win' => 'Victoire Domicile (1)',
                                                        'draw' => 'Match Nul (X)',
                                                        'away_win' => 'Victoire Extérieur (2)',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Libellé')
                                                    ->required()
                                                    ->maxLength(100),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('odd')
                                                    ->label('Cote')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->minValue(1.01)
                                                    ->maxValue(100)
                                                    ->required()
                                                    ->suffix('x'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                                    ]),

                                // ⚽ Les deux équipes marquent
                                Forms\Components\Tabs\Tab::make('Les deux marquent')
                                    ->icon('heroicon-o-check-circle')
                                    ->schema([
                                        Forms\Components\Repeater::make('odds.both_teams_score')
                                            ->label('Cotes pour les deux équipes marquent')
                                            ->schema([
                                                Forms\Components\Select::make('key')
                                                    ->label('Option')
                                                    ->options([
                                                        'yes' => 'Oui',
                                                        'no' => 'Non',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Libellé')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required(),
                                                Forms\Components\TextInput::make('odd')
                                                    ->label('Cote')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->required()
                                                    ->suffix('x'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->collapsible(),
                                    ]),

                                // 📊 Over/Under
                                Forms\Components\Tabs\Tab::make('Over/Under')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        Forms\Components\Repeater::make('odds.over_under')
                                            ->label('Cotes pour Over/Under')
                                            ->schema([
                                                Forms\Components\Select::make('key')
                                                    ->label('Option')
                                                    ->options([
                                                        'over_1_5' => 'Plus de 1.5',
                                                        'under_1_5' => 'Moins de 1.5',
                                                        'over_2_5' => 'Plus de 2.5',
                                                        'under_2_5' => 'Moins de 2.5',
                                                        'over_3_5' => 'Plus de 3.5',
                                                        'under_3_5' => 'Moins de 3.5',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Libellé')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required(),
                                                Forms\Components\TextInput::make('odd')
                                                    ->label('Cote')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->required()
                                                    ->suffix('x'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->collapsible(),
                                    ]),

                                // 🔀 Double Chance
                                Forms\Components\Tabs\Tab::make('Double Chance')
                                    ->icon('heroicon-o-arrows-right-left')
                                    ->schema([
                                        Forms\Components\Repeater::make('odds.double_chance')
                                            ->label('Cotes pour Double Chance')
                                            ->schema([
                                                Forms\Components\Select::make('key')
                                                    ->label('Option')
                                                    ->options([
                                                        '1X' => '1X (Domicile ou Nul)',
                                                        'X2' => 'X2 (Nul ou Extérieur)',
                                                        '12' => '12 (Domicile ou Extérieur)',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Libellé')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required(),
                                                Forms\Components\TextInput::make('odd')
                                                    ->label('Cote')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->required()
                                                    ->suffix('x'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->collapsible(),
                                    ]),

                                // 🎲 Score Exact
                                Forms\Components\Tabs\Tab::make('Score Exact')
                                    ->icon('heroicon-o-hashtag')
                                    ->schema([
                                        Forms\Components\Repeater::make('odds.exact_score')
                                            ->label('Cotes pour Score Exact')
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Clé (ex: 2_1 pour 2-1)')
                                                    ->required()
                                                    ->helperText('Format: score_home_score_away (ex: 2_1)'),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Libellé (ex: 2-1)')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required(),
                                                Forms\Components\TextInput::make('odd')
                                                    ->label('Cote')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->required()
                                                    ->suffix('x'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->collapsible(),
                                    ]),

                                // ⏱️ Mi-temps
                                Forms\Components\Tabs\Tab::make('Mi-temps')
                                    ->icon('heroicon-o-clock')
                                    ->schema([
                                        Forms\Components\Repeater::make('odds.first_half')
                                            ->label('Cotes pour le résultat à la mi-temps')
                                            ->schema([
                                                Forms\Components\Select::make('key')
                                                    ->label('Option')
                                                    ->options([
                                                        'home_win' => 'Domicile gagne 1ère MT',
                                                        'draw' => 'Nul à la mi-temps',
                                                        'away_win' => 'Extérieur gagne 1ère MT',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Libellé')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required(),
                                                Forms\Components\TextInput::make('odd')
                                                    ->label('Cote')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->required()
                                                    ->suffix('x'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->collapsible(),
                                    ]),

                                // ⚖️ Handicap
                                Forms\Components\Tabs\Tab::make('Handicap')
                                    ->icon('heroicon-o-scale')
                                    ->schema([
                                        Forms\Components\Repeater::make('odds.handicap')
                                            ->label('Cotes pour Handicap')
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Clé (ex: home_minus_1)')
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Libellé (ex: Domicile -1)')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(2)
                                                    ->required(),
                                                Forms\Components\TextInput::make('odd')
                                                    ->label('Cote')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->required()
                                                    ->suffix('x'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->collapsible(),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->persistTabInQueryString(),
                    ])
                    ->collapsible(),

                // ==================== SCÉNARIO DU MATCH ====================
                Forms\Components\Section::make('Scénario du Match')
                    ->description('⚠️ IMPORTANT : Configurez ici ce qui va réellement se passer dans le match (résultat, scores, événements)')
                    ->schema([
                        Forms\Components\Placeholder::make('scenario_info')
                            ->label('')
                            ->content('Cette section définit le VRAI résultat du match. Les utilisateurs parient sur ce résultat, mais ne le voient qu\'après le match.')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('result')
                            ->label('🏆 Vainqueur du match')
                            ->options([
                                'home_win' => '🏠 Victoire Équipe Domicile',
                                'away_win' => '✈️ Victoire Équipe Extérieur',
                                'draw' => '🤝 Match Nul',
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpanFull()
                            ->helperText('Sélectionnez qui va gagner ce match'),

                        // Scores selon le sport
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Fieldset::make('Score Final Attendu')
                                    ->schema([
                                        Forms\Components\TextInput::make('expected_score_home')
                                            ->label(fn (Forms\Get $get) => match ($get('sport_type') ?? 'football') {
                                                'football' => 'Buts domicile (attendus)',
                                                'basketball' => 'Points domicile (attendus)',
                                                'tennis' => 'Sets domicile (attendus)',
                                                default => 'Score domicile (attendu)',
                                            })
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(fn (Forms\Get $get) => match ($get('sport_type') ?? 'football') {
                                                'football' => 15,
                                                'basketball' => 200,
                                                'tennis' => 3,
                                                default => 20,
                                            })
                                            ->required()
                                            ->default(0)
                                            ->helperText('Score final que le match doit atteindre'),

                                        Forms\Components\TextInput::make('expected_score_away')
                                            ->label(fn (Forms\Get $get) => match ($get('sport_type') ?? 'football') {
                                                'football' => 'Buts extérieur (attendus)',
                                                'basketball' => 'Points extérieur (attendus)',
                                                'tennis' => 'Sets extérieur (attendus)',
                                                default => 'Score extérieur (attendu)',
                                            })
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(fn (Forms\Get $get) => match ($get('sport_type') ?? 'football') {
                                                'football' => 15,
                                                'basketball' => 200,
                                                'tennis' => 3,
                                                default => 20,
                                            })
                                            ->required()
                                            ->default(0)
                                            ->helperText('Score final que le match doit atteindre'),
                                    ]),
                            ]),

                        // FOOTBALL - Scores détaillés
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Fieldset::make('Score 1ère mi-temps (Attendu)')
                                    ->schema([
                                        Forms\Components\TextInput::make('expected_score_first_half_home')
                                            ->label('Buts domicile')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(10)
                                            ->default(0)
                                            ->helperText('Buts inscrits en 1ère MT'),

                                        Forms\Components\TextInput::make('expected_score_first_half_away')
                                            ->label('Buts extérieur')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(10)
                                            ->default(0)
                                            ->helperText('Buts inscrits en 1ère MT'),
                                    ]),

                                Forms\Components\Fieldset::make('Score 2ème mi-temps (Attendu)')
                                    ->schema([
                                        Forms\Components\TextInput::make('expected_score_second_half_home')
                                            ->label('Buts domicile')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(10)
                                            ->default(0)
                                            ->helperText('Buts inscrits en 2ème MT (NON cumulé)'),

                                        Forms\Components\TextInput::make('expected_score_second_half_away')
                                            ->label('Buts extérieur')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(10)
                                            ->default(0)
                                            ->helperText('Buts inscrits en 2ème MT (NON cumulé)'),
                                    ]),
                            ])
                            ->visible(fn (Forms\Get $get) => $get('sport_type') === 'football'),

                        // FOOTBALL - Prolongation et tirs au but
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_extra_time')
                                    ->label('🕐 Prolongation')
                                    ->reactive()
                                    ->helperText('Activer si le match va en prolongation'),

                                Forms\Components\Toggle::make('has_penalties')
                                    ->label('🥅 Tirs au but')
                                    ->reactive()
                                    ->helperText('Activer si le match va aux tirs au but'),
                            ])
                            ->visible(fn (Forms\Get $get) => $get('sport_type') === 'football' && $get('result') === 'draw'),

                        Forms\Components\Fieldset::make('Score Prolongation')
                            ->schema([
                                Forms\Components\TextInput::make('score_extra_time_home')
                                    ->label('Buts domicile (prolongation)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(5)
                                    ->default(0),

                                Forms\Components\TextInput::make('score_extra_time_away')
                                    ->label('Buts extérieur (prolongation)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(5)
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->visible(fn (Forms\Get $get) => $get('sport_type') === 'football' && $get('has_extra_time') === true),

                        Forms\Components\Fieldset::make('Tirs au but')
                            ->schema([
                                Forms\Components\TextInput::make('score_penalties_home')
                                    ->label('Score domicile (tirs au but)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->default(0),

                                Forms\Components\TextInput::make('score_penalties_away')
                                    ->label('Score extérieur (tirs au but)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->visible(fn (Forms\Get $get) => $get('sport_type') === 'football' && $get('has_penalties') === true),

                        // BASKETBALL - Quart-temps
                        Forms\Components\Repeater::make('quarter_scores')
                            ->label('Scores par quart-temps')
                            ->schema([
                                Forms\Components\Select::make('quarter')
                                    ->label('Quart-temps')
                                    ->options([
                                        1 => '1er quart-temps',
                                        2 => '2ème quart-temps',
                                        3 => '3ème quart-temps',
                                        4 => '4ème quart-temps',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('home_points')
                                    ->label('Points domicile')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),

                                Forms\Components\TextInput::make('away_points')
                                    ->label('Points extérieur')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->visible(fn (Forms\Get $get) => $get('sport_type') === 'basketball')
                            ->helperText('Ajoutez les scores de chaque quart-temps'),

                        // TENNIS - Sets
                        Forms\Components\Repeater::make('set_scores')
                            ->label('Scores par set')
                            ->schema([
                                Forms\Components\Select::make('set')
                                    ->label('Set')
                                    ->options([
                                        1 => '1er set',
                                        2 => '2ème set',
                                        3 => '3ème set',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('home_games')
                                    ->label('Jeux domicile')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(7)
                                    ->required(),

                                Forms\Components\TextInput::make('away_games')
                                    ->label('Jeux extérieur')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(7)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->visible(fn (Forms\Get $get) => $get('sport_type') === 'tennis')
                            ->helperText('Ajoutez les scores de chaque set'),

                        // ÉVÉNEMENTS DU MATCH
                        Forms\Components\Repeater::make('match_events')
                            ->label('📋 Événements du match')
                            ->schema([
                                Forms\Components\Select::make('event_type')
                                    ->label('Type')
                                    ->options(function (Forms\Get $get) {
                                        $sportType = $get('../../../sport_type') ?? 'football';

                                        return match ($sportType) {
                                            'football' => [
                                                'goal' => '⚽ But',
                                                'yellow_card' => '🟨 Carton jaune',
                                                'red_card' => '🟥 Carton rouge',
                                                'penalty' => '🎯 Penalty',
                                                'corner' => '🚩 Corner',
                                                'offside' => '🚫 Hors-jeu',
                                            ],
                                            'basketball' => [
                                                '2_points' => '🏀 Panier 2 points',
                                                '3_points' => '🎯 Panier 3 points',
                                                'free_throw' => '🎁 Lancer franc',
                                                'foul' => '⚠️ Faute',
                                                'timeout' => '⏸️ Temps mort',
                                            ],
                                            'tennis' => [
                                                'ace' => '🎾 Ace',
                                                'double_fault' => '❌ Double faute',
                                                'break_point' => '💥 Break',
                                            ],
                                            default => [],
                                        };
                                    })
                                    ->required(),

                                Forms\Components\Select::make('team')
                                    ->label('Équipe')
                                    ->options([
                                        'home' => 'Domicile',
                                        'away' => 'Extérieur',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('minute')
                                    ->label('Minute')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(fn (Forms\Get $get) => $get('../../../duration') ?? 90)
                                    ->suffix('min')
                                    ->required(),

                                Forms\Components\TextInput::make('player')
                                    ->label('Joueur (optionnel)')
                                    ->maxLength(100),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->helperText('Ajoutez les événements qui se produiront pendant le match (buts, cartons, etc.)')
                            ->visible(fn (Forms\Get $get) => !in_array($get('status'), ['completed', 'cancelled'])),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->visible(fn (Forms\Get $get) => $get('status') !== 'cancelled'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('team_home_logo')
                    ->label('🏠')
                    ->disk('game_images')
                    ->height(50)
                    ->width(50)
                    ->circular(),

                Tables\Columns\TextColumn::make('match')
                    ->label('Match')
                    ->searchable(['team_home', 'team_away'])
                    ->formatStateUsing(fn (VirtualMatch $record) => $record->team_home . ' vs ' . $record->team_away)
                    ->description(fn (VirtualMatch $record) => $record->league)
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\ImageColumn::make('team_away_logo')
                    ->label('✈️')
                    ->disk('game_images')
                    ->height(50)
                    ->width(50)
                    ->circular(),

                Tables\Columns\TextColumn::make('sport_type')
                    ->label('Sport')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'football' => 'success',
                        'basketball' => 'warning',
                        'tennis' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Durée')
                    ->formatStateUsing(fn (int $state) => $state . ' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (VirtualMatchStatus $state): string => $state->color())
                    ->formatStateUsing(fn (VirtualMatchStatus $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(function (VirtualMatch $record) {
                        if ($record->status === VirtualMatchStatus::UPCOMING) {
                            return '-';
                        }
                        return $record->score_home . ' - ' . $record->score_away;
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn (VirtualMatch $record): string =>
                        $record->starts_at->format('d/m/Y H:i')
                    ),

                Tables\Columns\TextColumn::make('bets_count')
                    ->label('Paris')
                    ->counts('bets')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'upcoming' => 'À venir',
                        'live' => 'En direct',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ]),

                SelectFilter::make('league')
                    ->label('Ligue')
                    ->options([
                        'Premier League' => 'Premier League',
                        'La Liga' => 'La Liga',
                        'Serie A' => 'Serie A',
                        'Bundesliga' => 'Bundesliga',
                        'Ligue 1' => 'Ligue 1',
                        'Champions League' => 'Champions League',
                        'Europa League' => 'Europa League',
                    ]),

                SelectFilter::make('sport_type')
                    ->label('Sport')
                    ->options([
                        'football' => 'Football',
                        'basketball' => 'Basketball',
                        'tennis' => 'Tennis',
                    ]),

                SelectFilter::make('duration')
                    ->label('Durée')
                    ->options([
                        1 => '1 minute',
                        3 => '3 minutes',
                        5 => '5 minutes',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('start')
                    ->label('Démarrer')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (VirtualMatch $record) => $record->start())
                    ->visible(fn (VirtualMatch $record) => $record->status === VirtualMatchStatus::UPCOMING),

                Tables\Actions\Action::make('complete')
                    ->label('Terminer')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (VirtualMatch $record) => $record->complete())
                    ->visible(fn (VirtualMatch $record) => $record->status === VirtualMatchStatus::LIVE),

                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun match virtuel')
            ->emptyStateDescription('Créez votre premier match virtuel pour commencer.')
            ->emptyStateIcon('heroicon-o-fire');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVirtualMatches::route('/'),
            'create' => Pages\CreateVirtualMatch::route('/create'),
            'edit' => Pages\EditVirtualMatch::route('/{record}/edit'),
            // 'view' => Pages\ViewVirtualMatch::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'upcoming')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
