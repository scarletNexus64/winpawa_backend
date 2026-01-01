<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class Settings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Configuration';

    protected static string $view = 'filament.pages.settings';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // Affiliation
            'affiliate_commission_deposit' => Setting::get('affiliate_commission_deposit', 5),
            'affiliate_commission_loss' => Setting::get('affiliate_commission_loss', 25),
            'affiliate_min_withdrawal' => Setting::get('affiliate_min_withdrawal', 5000),

            // Transactions
            'min_deposit' => Setting::get('min_deposit', 200),
            'min_withdrawal' => Setting::get('min_withdrawal', 1000),
            'signup_bonus_percent' => Setting::get('signup_bonus_percent', 50),
            'wagering_requirement' => Setting::get('wagering_requirement', 5),

            // Maintenance
            'site_maintenance' => Setting::get('site_maintenance', false),
            'maintenance_message' => Setting::get('maintenance_message', 'Site en maintenance. Nous revenons bientôt!'),

            // Site Info
            'site_name' => Setting::get('site_name', 'WINPAWA'),
            'site_description' => Setting::get('site_description', 'Plateforme de Casino Gaming'),
            'support_email' => Setting::get('support_email', 'support@winpawa.com'),
            'support_phone' => Setting::get('support_phone', '+237 6XX XX XX XX'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informations Générales')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('site_name')
                                            ->label('Nom du site')
                                            ->required(),

                                        Forms\Components\Textarea::make('site_description')
                                            ->label('Description')
                                            ->rows(3),

                                        Forms\Components\TextInput::make('support_email')
                                            ->label('Email de support')
                                            ->email()
                                            ->required(),

                                        Forms\Components\TextInput::make('support_phone')
                                            ->label('Téléphone de support')
                                            ->tel(),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Affiliation')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Section::make('Commissions d\'affiliation')
                                    ->description('Configuration des taux de commission pour le programme d\'affiliation')
                                    ->schema([
                                        Forms\Components\TextInput::make('affiliate_commission_deposit')
                                            ->label('Commission sur dépôt (%)')
                                            ->helperText('Commission versée à l\'affilié quand un filleul fait un dépôt')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(5)
                                            ->required(),

                                        Forms\Components\TextInput::make('affiliate_commission_loss')
                                            ->label('Commission sur pertes (%)')
                                            ->helperText('Commission versée à l\'affilié sur les pertes du filleul')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(25)
                                            ->required(),

                                        Forms\Components\TextInput::make('affiliate_min_withdrawal')
                                            ->label('Retrait minimum (FCFA)')
                                            ->helperText('Montant minimum que l\'affilié peut retirer')
                                            ->numeric()
                                            ->prefix('FCFA')
                                            ->minValue(0)
                                            ->default(5000)
                                            ->required(),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Transactions')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Limites de transactions')
                                    ->description('Configuration des montants minimum pour dépôts et retraits')
                                    ->schema([
                                        Forms\Components\TextInput::make('min_deposit')
                                            ->label('Dépôt minimum (FCFA)')
                                            ->numeric()
                                            ->prefix('FCFA')
                                            ->minValue(0)
                                            ->default(200)
                                            ->required(),

                                        Forms\Components\TextInput::make('min_withdrawal')
                                            ->label('Retrait minimum (FCFA)')
                                            ->numeric()
                                            ->prefix('FCFA')
                                            ->minValue(0)
                                            ->default(1000)
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Bonus d\'inscription')
                                    ->description('Configuration du bonus de bienvenue')
                                    ->schema([
                                        Forms\Components\TextInput::make('signup_bonus_percent')
                                            ->label('Pourcentage du bonus (%)')
                                            ->helperText('Pourcentage du premier dépôt offert en bonus')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(200)
                                            ->default(50)
                                            ->required(),

                                        Forms\Components\TextInput::make('wagering_requirement')
                                            ->label('Exigence de mise (x)')
                                            ->helperText('Nombre de fois que le bonus doit être misé avant retrait')
                                            ->numeric()
                                            ->suffix('x')
                                            ->minValue(1)
                                            ->default(5)
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Maintenance')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->badge(Setting::get('site_maintenance', false) ? 'ACTIF' : null)
                            ->badgeColor('danger')
                            ->schema([
                                Forms\Components\Section::make('Mode Maintenance Global')
                                    ->description('Activer le mode maintenance met le site hors ligne pour tous les utilisateurs')
                                    ->schema([
                                        Forms\Components\Toggle::make('site_maintenance')
                                            ->label('Activer le mode maintenance')
                                            ->helperText('Le site ne sera accessible qu\'aux administrateurs')
                                            ->live()
                                            ->default(false),

                                        Forms\Components\Textarea::make('maintenance_message')
                                            ->label('Message de maintenance')
                                            ->helperText('Message affiché aux utilisateurs pendant la maintenance')
                                            ->rows(4)
                                            ->visible(fn (Forms\Get $get) => $get('site_maintenance'))
                                            ->required(fn (Forms\Get $get) => $get('site_maintenance')),
                                    ]),

                                Forms\Components\Section::make('Maintenance par jeu')
                                    ->description('Pour mettre un jeu spécifique en maintenance, allez dans Gestion des Jeux > Modifier le jeu')
                                    ->schema([
                                        Forms\Components\Placeholder::make('info')
                                            ->label('')
                                            ->content('Vous pouvez activer/désactiver la maintenance pour chaque jeu individuellement dans la page de gestion des jeux.'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Sauvegarder les settings généraux
        Setting::set('site_name', $data['site_name'], 'string', 'general');
        Setting::set('site_description', $data['site_description'], 'string', 'general');
        Setting::set('support_email', $data['support_email'], 'string', 'general');
        Setting::set('support_phone', $data['support_phone'], 'string', 'general');

        // Sauvegarder les settings d'affiliation
        Setting::set('affiliate_commission_deposit', $data['affiliate_commission_deposit'], 'integer', 'affiliate');
        Setting::set('affiliate_commission_loss', $data['affiliate_commission_loss'], 'integer', 'affiliate');
        Setting::set('affiliate_min_withdrawal', $data['affiliate_min_withdrawal'], 'integer', 'affiliate');

        // Sauvegarder les settings de transactions
        Setting::set('min_deposit', $data['min_deposit'], 'integer', 'transaction');
        Setting::set('min_withdrawal', $data['min_withdrawal'], 'integer', 'transaction');
        Setting::set('signup_bonus_percent', $data['signup_bonus_percent'], 'integer', 'transaction');
        Setting::set('wagering_requirement', $data['wagering_requirement'], 'integer', 'transaction');

        // Sauvegarder les settings de maintenance
        Setting::set('site_maintenance', $data['site_maintenance'], 'boolean', 'maintenance');
        Setting::set('maintenance_message', $data['maintenance_message'] ?? '', 'string', 'maintenance');

        Notification::make()
            ->success()
            ->title('Configuration sauvegardée')
            ->body('Les paramètres ont été mis à jour avec succès.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Enregistrer les modifications')
                ->submit('save')
                ->color('primary')
                ->icon('heroicon-o-check'),
        ];
    }
}
