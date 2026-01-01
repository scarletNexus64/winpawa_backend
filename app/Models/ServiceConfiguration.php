<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ServiceConfiguration extends Model
{
    protected $fillable = [
        'service_type', 'is_active', 'config',
        'whatsapp_api_token', 'whatsapp_phone_number_id', 'whatsapp_api_version',
        'whatsapp_template_name', 'whatsapp_language',
        'nexah_base_url', 'nexah_send_endpoint', 'nexah_credits_endpoint',
        'nexah_user', 'nexah_password', 'nexah_sender_id',
        'freemopay_base_url', 'freemopay_app_key', 'freemopay_secret_key',
        'freemopay_callback_url', 'freemopay_init_payment_timeout',
        'freemopay_status_check_timeout', 'freemopay_token_timeout',
        'freemopay_token_cache_duration', 'freemopay_max_retries',
        'freemopay_retry_delay', 'coinbase_api_key', 'coinbase_webhook_secret',
        'coinbase_api_version', 'default_notification_channel',
        'google_client_id', 'google_client_secret', 'google_redirect_url', 'google_scopes',
        'firebase_credentials', 'firebase_project_id', 'firebase_server_key',
        'firebase_sender_id', 'firebase_api_key', 'firebase_database_url',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'freemopay_init_payment_timeout' => 'integer',
        'freemopay_status_check_timeout' => 'integer',
        'freemopay_token_timeout' => 'integer',
        'freemopay_token_cache_duration' => 'integer',
        'freemopay_max_retries' => 'integer',
        'freemopay_retry_delay' => 'decimal:1',
        'google_scopes' => 'array',
    ];

    public static function getConfig(string $serviceType): ?self
    {
        return Cache::remember("service_config_{$serviceType}", now()->addHours(1),
            fn() => self::where('service_type', $serviceType)->first());
    }

    public static function getWhatsAppConfig(): ?self { return self::getConfig('whatsapp'); }
    public static function getNexahConfig(): ?self { return self::getConfig('nexah_sms'); }
    public static function getFreeMoPayConfig(): ?self { return self::getConfig('freemopay'); }
    public static function getCoinbaseConfig(): ?self { return self::getConfig('coinbase'); }
    public static function getGoogleOAuthConfig(): ?self { return self::getConfig('google_oauth'); }
    public static function getFirebaseConfig(): ?self { return self::getConfig('firebase'); }

    public static function clearCache(?string $serviceType = null): void
    {
        if ($serviceType) {
            Cache::forget("service_config_{$serviceType}");
        } else {
            foreach (['whatsapp', 'nexah_sms', 'freemopay', 'coinbase', 'google_oauth', 'firebase'] as $type) {
                Cache::forget("service_config_{$type}");
            }
        }
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(fn($c) => self::clearCache($c->service_type));
        static::deleted(fn($c) => self::clearCache($c->service_type));
    }

    public function isConfigured(): bool
    {
        if (!$this->is_active) return false;
        $errors = match($this->service_type) {
            'whatsapp' => $this->validateWhatsAppConfig(),
            'nexah_sms' => $this->validateNexahConfig(),
            'freemopay' => $this->validateFreeMoPayConfig(),
            'coinbase' => $this->validateCoinbaseConfig(),
            'google_oauth' => $this->validateGoogleOAuthConfig(),
            'firebase' => $this->validateFirebaseConfig(),
            default => [],
        };
        return empty($errors);
    }

    public function validateWhatsAppConfig(): array
    {
        $errors = [];
        if (empty($this->whatsapp_api_token)) $errors[] = 'API Token requis';
        if (empty($this->whatsapp_phone_number_id)) $errors[] = 'Phone Number ID requis';
        if (empty($this->whatsapp_template_name)) $errors[] = 'Template Name requis';
        return $errors;
    }

    public function validateNexahConfig(): array
    {
        $errors = [];
        if (empty($this->nexah_base_url)) $errors[] = 'Base URL requis';
        if (empty($this->nexah_user)) $errors[] = 'User requis';
        if (empty($this->nexah_password)) $errors[] = 'Password requis';
        if (empty($this->nexah_sender_id)) $errors[] = 'Sender ID requis';
        return $errors;
    }

    public function validateFreeMoPayConfig(): array
    {
        $errors = [];
        if (empty($this->freemopay_app_key)) $errors[] = 'App Key requis';
        if (empty($this->freemopay_secret_key)) $errors[] = 'Secret Key requis';
        if (empty($this->freemopay_callback_url)) $errors[] = 'Callback URL requis';
        return $errors;
    }

    public function validateCoinbaseConfig(): array
    {
        $errors = [];
        if (empty($this->coinbase_api_key)) $errors[] = 'API Key requis';
        if (empty($this->coinbase_webhook_secret)) $errors[] = 'Webhook Secret requis';
        return $errors;
    }

    public function validateGoogleOAuthConfig(): array
    {
        $errors = [];
        if (empty($this->google_client_id)) $errors[] = 'Client ID requis';
        if (empty($this->google_client_secret)) $errors[] = 'Client Secret requis';
        if (empty($this->google_redirect_url)) $errors[] = 'Redirect URL requis';
        return $errors;
    }

    public function validateFirebaseConfig(): array
    {
        $errors = [];
        if (empty($this->firebase_project_id)) $errors[] = 'Project ID requis';
        if (empty($this->firebase_server_key)) $errors[] = 'Server Key requis';
        return $errors;
    }
}
