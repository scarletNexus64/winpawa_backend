<?php
namespace App\Services\Payment;

use App\Models\ServiceConfiguration;
use Illuminate\Support\Facades\Http;

class CoinbaseService
{
    protected ?ServiceConfiguration $config;

    public function __construct()
    {
        $this->config = ServiceConfiguration::getCoinbaseConfig();
    }

    public function testConnection(): array
    {
        try {
            if (!$this->config?.isConfigured()) {
                return ['success' => false, 'message' => 'Configuration Coinbase incomplète'];
            }
            
            $response = Http::withHeaders([
                'X-CC-Api-Key' => $this->config->coinbase_api_key,
                'X-CC-Version' => $this->config->coinbase_api_version,
            ])->timeout(10)->get('https://api.commerce.coinbase.com/charges');
            
            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connexion Coinbase réussie', 'data' => $response->json()];
            }
            
            return ['success' => false, 'message' => "Erreur {$response->status()}: " . $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function createCharge(array $data): array
    {
        try {
            if (!$this->config?.isConfigured()) {
                return ['success' => false, 'message' => 'Configuration incomplète'];
            }

            $response = Http::withHeaders([
                'X-CC-Api-Key' => $this->config->coinbase_api_key,
                'X-CC-Version' => $this->config->coinbase_api_version,
                'Content-Type' => 'application/json',
            ])->post('https://api.commerce.coinbase.com/charges', $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'message' => $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
