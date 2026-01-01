<?php
namespace App\Services\Payment;

use App\Models\ServiceConfiguration;

class FreeMoPayService
{
    protected ?ServiceConfiguration $config;
    protected FreeMoPayClient $client;
    protected FreeMoPayTokenManager $tokenManager;

    public function __construct()
    {
        $this->config = ServiceConfiguration::getFreeMoPayConfig();
        $this->client = new FreeMoPayClient();
        $this->tokenManager = new FreeMoPayTokenManager($this->client);
    }

    public function testConnection(): array
    {
        try {
            if (!$this->config?.isConfigured()) {
                return ['success' => false, 'message' => 'Configuration incomplète'];
            }
            
            $this->tokenManager->clearToken();
            $token = $this->tokenManager->getToken();
            
            return [
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => ['token_preview' => substr($token, 0, 20) . '...']
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
