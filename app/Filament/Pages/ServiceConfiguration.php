<?php

namespace App\Filament\Pages;

use App\Models\ServiceConfiguration as ServiceConfigModel;
use App\Services\Payment\FreeMoPayService;
use App\Services\Payment\CoinbaseService;
use App\Services\Notifications\NexahService;
use App\Services\Notifications\WhatsAppService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class ServiceConfiguration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configuration Services';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.service-configuration';

    public ?array $whatsappData = [];
    public ?array $nexahData = [];
    public ?array $freemopayData = [];
    public ?array $coinbaseData = [];
    public ?array $googleOauthData = [];
    public ?array $firebaseData = [];
    public string $activeTab = 'whatsapp';

    public function mount(): void
    {
        $whatsapp = ServiceConfigModel::getWhatsAppConfig();
        $nexah = ServiceConfigModel::getNexahConfig();
        $freemopay = ServiceConfigModel::getFreeMoPayConfig();
        $coinbase = ServiceConfigModel::getCoinbaseConfig();
        $googleOauth = ServiceConfigModel::getGoogleOAuthConfig();
        $firebase = ServiceConfigModel::getFirebaseConfig();

        $this->whatsappForm->fill($whatsapp?->toArray() ?? ['service_type' => 'whatsapp', 'is_active' => true]);
        $this->nexahForm->fill($nexah?->toArray() ?? ['service_type' => 'nexah_sms', 'is_active' => true]);
        $this->freemopayForm->fill($freemopay?->toArray() ?? ['service_type' => 'freemopay', 'is_active' => true]);
        $this->coinbaseForm->fill($coinbase?->toArray() ?? ['service_type' => 'coinbase', 'is_active' => true]);
        $this->googleOauthForm->fill($googleOauth?->toArray() ?? ['service_type' => 'google_oauth', 'is_active' => true]);
        $this->firebaseForm->fill($firebase?->toArray() ?? ['service_type' => 'firebase', 'is_active' => true]);
    }

    protected function getForms(): array
    {
        return ['whatsappForm', 'nexahForm', 'freemopayForm', 'coinbaseForm', 'googleOauthForm', 'firebaseForm'];
    }

    public function whatsappForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('is_active')->label('Actif'),
            Forms\Components\TextInput::make('whatsapp_api_token')->label('API Token')->required()->password(),
            Forms\Components\TextInput::make('whatsapp_phone_number_id')->label('Phone Number ID')->required(),
            Forms\Components\TextInput::make('whatsapp_api_version')->label('API Version')->default('v21.0')->required(),
            Forms\Components\TextInput::make('whatsapp_template_name')->label('Template Name')->required(),
            Forms\Components\TextInput::make('whatsapp_language')->label('Language')->default('fr')->required(),
        ])->statePath('whatsappData');
    }

    public function nexahForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('is_active')->label('Actif'),
            Forms\Components\TextInput::make('nexah_base_url')->label('Base URL')->url()->required(),
            Forms\Components\TextInput::make('nexah_user')->label('User')->required(),
            Forms\Components\TextInput::make('nexah_password')->label('Password')->password()->required(),
            Forms\Components\TextInput::make('nexah_sender_id')->label('Sender ID')->required(),
        ])->statePath('nexahData');
    }

    public function freemopayForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('is_active')->label('Actif'),
            Forms\Components\TextInput::make('freemopay_base_url')->label('Base URL')->url()->default('https://api-v2.freemopay.com')->required(),
            Forms\Components\TextInput::make('freemopay_app_key')->label('App Key')->required()->password(),
            Forms\Components\TextInput::make('freemopay_secret_key')->label('Secret Key')->required()->password(),
            Forms\Components\TextInput::make('freemopay_callback_url')->label('Callback URL')->url()->required(),
        ])->statePath('freemopayData');
    }

    public function coinbaseForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('is_active')->label('Actif'),
            Forms\Components\TextInput::make('coinbase_api_key')->label('API Key')->required()->password(),
            Forms\Components\TextInput::make('coinbase_webhook_secret')->label('Webhook Secret')->required()->password(),
            Forms\Components\TextInput::make('coinbase_api_version')->label('API Version')->default('2018-03-22')->required(),
        ])->statePath('coinbaseData');
    }

    public function saveWhatsApp()
    {
        $data = $this->whatsappForm->getState();
        ServiceConfigModel::updateOrCreate(['service_type' => 'whatsapp'], $data);
        Notification::make()->success()->title('WhatsApp configuré')->send();
    }

    public function saveNexah()
    {
        $data = $this->nexahForm->getState();
        ServiceConfigModel::updateOrCreate(['service_type' => 'nexah_sms'], $data);
        Notification::make()->success()->title('Nexah SMS configuré')->send();
    }

    public function saveFreemopay()
    {
        $data = $this->freemopayForm->getState();
        ServiceConfigModel::updateOrCreate(['service_type' => 'freemopay'], $data);
        Notification::make()->success()->title('FreeMoPay configuré')->send();
    }

    public function saveCoinbase()
    {
        $data = $this->coinbaseForm->getState();
        ServiceConfigModel::updateOrCreate(['service_type' => 'coinbase'], $data);
        Notification::make()->success()->title('Coinbase configuré')->send();
    }

    public function testWhatsApp()
    {
        $service = new WhatsAppService();
        $result = $service->testConnection();
        Notification::make()->title($result['success'] ? 'Test réussi' : 'Test échoué')
            ->body($result['message'])->{$result['success'] ? 'success' : 'danger'}()->send();
    }

    public function testNexah()
    {
        $service = new NexahService();
        $result = $service->testConnection();
        Notification::make()->title($result['success'] ? 'Test réussi' : 'Test échoué')
            ->body($result['message'] . ($result['credit'] ?? ''))->{$result['success'] ? 'success' : 'danger'}()->send();
    }

    public function testFreemopay()
    {
        $service = new FreeMoPayService();
        $result = $service->testConnection();
        Notification::make()->title($result['success'] ? 'Test réussi' : 'Test échoué')
            ->body($result['message'])->{$result['success'] ? 'success' : 'danger'}()->send();
    }

    public function testCoinbase()
    {
        $service = new CoinbaseService();
        $result = $service->testConnection();
        Notification::make()->title($result['success'] ? 'Test réussi' : 'Test échoué')
            ->body($result['message'])->{$result['success'] ? 'success' : 'danger'}()->send();
    }

    public function googleOauthForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('is_active')->label('Actif'),
            Forms\Components\TextInput::make('google_client_id')
                ->label('Client ID')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('google_client_secret')
                ->label('Client Secret')
                ->required()
                ->password()
                ->maxLength(255),
            Forms\Components\TextInput::make('google_redirect_url')
                ->label('Redirect URL')
                ->required()
                ->url()
                ->maxLength(255)
                ->helperText('URL de redirection après authentification'),
            Forms\Components\TagsInput::make('google_scopes')
                ->label('Scopes')
                ->placeholder('Ajouter un scope')
                ->default(['email', 'profile'])
                ->helperText('Ex: email, profile, openid'),
        ])->statePath('googleOauthData');
    }

    public function firebaseForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('is_active')->label('Actif'),
            Forms\Components\Textarea::make('firebase_credentials')
                ->label('Credentials JSON')
                ->rows(5)
                ->helperText('Contenu du fichier JSON de credentials Firebase')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('firebase_project_id')
                ->label('Project ID')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('firebase_server_key')
                ->label('Server Key')
                ->required()
                ->password()
                ->maxLength(255)
                ->helperText('Cloud Messaging Server Key'),
            Forms\Components\TextInput::make('firebase_sender_id')
                ->label('Sender ID')
                ->maxLength(255),
            Forms\Components\TextInput::make('firebase_api_key')
                ->label('API Key')
                ->password()
                ->maxLength(255),
            Forms\Components\TextInput::make('firebase_database_url')
                ->label('Database URL')
                ->url()
                ->maxLength(255)
                ->helperText('Ex: https://your-project.firebaseio.com'),
        ])->statePath('firebaseData');
    }

    public function saveGoogleOauth()
    {
        $data = $this->googleOauthForm->getState();
        ServiceConfigModel::updateOrCreate(['service_type' => 'google_oauth'], $data);
        Notification::make()->success()->title('Google OAuth configuré')->send();
    }

    public function saveFirebase()
    {
        $data = $this->firebaseForm->getState();
        ServiceConfigModel::updateOrCreate(['service_type' => 'firebase'], $data);
        Notification::make()->success()->title('Firebase configuré')->send();
    }

    public function testGoogleOauth()
    {
        $config = ServiceConfigModel::getGoogleOAuthConfig();
        if ($config && $config->isConfigured()) {
            Notification::make()->success()
                ->title('Test réussi')
                ->body('Configuration Google OAuth valide')
                ->send();
        } else {
            Notification::make()->danger()
                ->title('Test échoué')
                ->body('Configuration incomplète')
                ->send();
        }
    }

    public function testFirebase()
    {
        $config = ServiceConfigModel::getFirebaseConfig();
        if ($config && $config->isConfigured()) {
            Notification::make()->success()
                ->title('Test réussi')
                ->body('Configuration Firebase valide')
                ->send();
        } else {
            Notification::make()->danger()
                ->title('Test échoué')
                ->body('Configuration incomplète')
                ->send();
        }
    }
}
