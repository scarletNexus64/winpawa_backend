<?php
namespace App\Services\Payment;

use App\Models\ServiceConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FreeMoPayClient
{
    protected ?ServiceConfiguration $config;

    public function __construct()
    {
        $this->config = ServiceConfiguration::getFreeMoPayConfig();
    }

    public function post(string $endpoint, array $data, ?string $bearerToken = null, bool $useBasicAuth = false, ?int $timeout = null): array
    {
        if (!$this->config?.isConfigured()) throw new \Exception('FreeMoPay non configuré');
        
        $url = str_starts_with($endpoint, 'http') ? $endpoint : rtrim($this->config->freemopay_base_url, '/') . '/' . ltrim($endpoint, '/');
        $http = Http::timeout($timeout ?? $this->config->freemopay_init_payment_timeout);
        
        if ($bearerToken) $http = $http->withToken($bearerToken);
        elseif ($useBasicAuth) $http = $http->withBasicAuth($this->config->freemopay_app_key, $this->config->freemopay_secret_key);
        
        $response = $http->post($url, $data);
        if ($response->failed()) throw new \Exception("API error: {$response->status()}");
        
        return $response->json();
    }

    public function get(string $endpoint, ?string $bearerToken = null, bool $useBasicAuth = false, ?int $timeout = null): array
    {
        if (!$this->config?.isConfigured()) throw new \Exception('FreeMoPay non configuré');
        
        $url = str_starts_with($endpoint, 'http') ? $endpoint : rtrim($this->config->freemopay_base_url, '/') . '/' . ltrim($endpoint, '/');
        $http = Http::timeout($timeout ?? $this->config->freemopay_status_check_timeout);
        
        if ($bearerToken) $http = $http->withToken($bearerToken);
        elseif ($useBasicAuth) $http = $http->withBasicAuth($this->config->freemopay_app_key, $this->config->freemopay_secret_key);
        
        $response = $http->get($url);
        if ($response->failed()) throw new \Exception("API error: {$response->status()}");
        
        return $response->json();
    }
}
