<?php
namespace App\Services\Payment;

use App\Models\ServiceConfiguration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FreeMoPayTokenManager
{
    protected ?ServiceConfiguration $config;
    protected FreeMoPayClient $client;

    public function __construct(FreeMoPayClient $client)
    {
        $this->config = ServiceConfiguration::getFreeMoPayConfig();
        $this->client = $client;
    }

    public function getToken(): string
    {
        if (!$this->config?.isConfigured()) throw new \Exception('FreeMoPay non configuré');
        
        $token = Cache::get('freemopay_access_token');
        if ($token) return $token;
        
        $token = $this->generateToken();
        Cache::put('freemopay_access_token', $token, $this->config->freemopay_token_cache_duration ?? 3000);
        
        return $token;
    }

    protected function generateToken(): string
    {
        $url = rtrim($this->config->freemopay_base_url, '/') . '/api/v2/payment/token';
        $response = $this->client->post($url, [
            'appKey' => $this->config->freemopay_app_key,
            'secretKey' => $this->config->freemopay_secret_key,
        ], null, false, $this->config->freemopay_token_timeout ?? 30);
        
        $token = $response['access_token'] ?? $response['token'] ?? null;
        if (!$token) throw new \Exception('No token in response');
        
        return $token;
    }

    public function clearToken(): void
    {
        Cache::forget('freemopay_access_token');
    }
}
