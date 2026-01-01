<?php
namespace App\Services\Notifications;

use App\Models\ServiceConfiguration;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected ?ServiceConfiguration $config;

    public function __construct()
    {
        $this->config = ServiceConfiguration::getWhatsAppConfig();
    }

    public function sendOtp(string $recipient, string $otpCode, ?string $templateName = null, ?string $languageCode = null): array
    {
        try {
            if (!$this->config?.isConfigured()) {
                return ['success' => false, 'message' => 'Configuration WhatsApp incomplète'];
            }
            
            $templateName = $templateName ?: $this->config->whatsapp_template_name;
            $languageCode = $languageCode ?: $this->config->whatsapp_language;
            $url = "https://graph.facebook.com/{$this->config->whatsapp_api_version}/{$this->config->whatsapp_phone_number_id}/messages";
            
            if (!str_starts_with($recipient, '+')) $recipient = '+' . $recipient;
            
            $response = Http::withToken($this->config->whatsapp_api_token)->timeout(30)->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => $languageCode],
                    'components' => [
                        ['type' => 'body', 'parameters' => [['type' => 'text', 'text' => $otpCode]]],
                        ['type' => 'button', 'sub_type' => 'url', 'index' => 0, 'parameters' => [['type' => 'text', 'text' => $otpCode]]]
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['messages'][0]['id'])) {
                    return ['success' => true, 'message' => 'Message envoyé', 'message_id' => $data['messages'][0]['id'], 'data' => $data];
                }
            }
            
            return ['success' => false, 'message' => "Erreur {$response->status()}", 'data' => $response->json()];
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
            
            $url = "https://graph.facebook.com/{$this->config->whatsapp_api_version}/{$this->config->whatsapp_phone_number_id}";
            $response = Http::withToken($this->config->whatsapp_api_token)->timeout(10)->get($url);
            
            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connexion réussie', 'data' => $response->json()];
            }
            
            return ['success' => false, 'message' => "Erreur {$response->status()}: " . $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
