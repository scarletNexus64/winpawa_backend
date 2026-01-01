<?php
namespace App\Services\Notifications;

use App\Models\ServiceConfiguration;
use Illuminate\Support\Facades\Http;

class NexahService
{
    protected ?ServiceConfiguration $config;

    public function __construct()
    {
        $this->config = ServiceConfiguration::getNexahConfig();
    }

    public function sendSms(string $recipient, string $message, ?string $senderId = null): array
    {
        try {
            if (!$this->config?.isConfigured()) {
                return ['success' => false, 'message' => 'Configuration Nexah incomplète'];
            }
            
            $url = $this->config->nexah_base_url . $this->config->nexah_send_endpoint;
            $response = Http::timeout(30)->post($url, [
                'user' => $this->config->nexah_user,
                'password' => $this->config->nexah_password,
                'senderid' => $senderId ?: $this->config->nexah_sender_id,
                'sms' => $message,
                'mobiles' => $recipient,
            ]);
            
            if ($response->successful()) {
                return ['success' => true, 'message' => 'SMS envoyé', 'data' => $response->json()];
            }
            
            return ['success' => false, 'message' => "Erreur {$response->status()}"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function testConnection(): array
    {
        try {
            if (!$this->config?.isConfigured()) {
                return ['success' => false, 'message' => 'Configuration incomplète'];
            }
            
            $url = $this->config->nexah_base_url . $this->config->nexah_credits_endpoint;
            $response = Http::timeout(30)->post($url, [
                'user' => $this->config->nexah_user,
                'password' => $this->config->nexah_password,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'credit' => $data['credit'] ?? null,
                    'data' => $data
                ];
            }
            
            return ['success' => false, 'message' => "Erreur {$response->status()}"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
