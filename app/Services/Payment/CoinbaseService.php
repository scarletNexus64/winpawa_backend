<?php
namespace App\Services\Payment;

use App\Models\Transaction;
use App\Models\ServiceConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CoinbaseService
{
    protected string $apiKey;
    protected string $apiVersion;
    protected string $baseUrl = 'https://api.commerce.coinbase.com';

    public function __construct()
    {
        // Essayer d'abord de récupérer depuis ServiceConfiguration (dashboard)
        $config = ServiceConfiguration::getCoinbaseConfig();

        if ($config && $config->is_active) {
            $this->apiKey = $config->coinbase_api_key ?? '';
            $this->apiVersion = $config->coinbase_api_version ?? '2018-03-22';
        } else {
            // Fallback sur les variables d'environnement
            $this->apiKey = config('services.coinbase.api_key', env('COINBASE_COMMERCE_API_KEY', ''));
            $this->apiVersion = config('services.coinbase.api_version', env('COINBASE_COMMERCE_API_VERSION', '2018-03-22'));
        }
    }

    /**
     * Tester la connexion à l'API Coinbase Commerce
     */
    public function testConnection(): array
    {
        try {
            if (empty($this->apiKey)) {
                return ['success' => false, 'message' => 'Clé API Coinbase non configurée'];
            }

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/charges");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion Coinbase Commerce réussie',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => "Erreur {$response->status()}: " . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Coinbase test connection error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Créer un charge (demande de paiement) pour un dépôt
     */
    public function createCharge(Transaction $transaction, string $currency = 'USD'): array
    {
        try {
            if (empty($this->apiKey)) {
                return ['success' => false, 'message' => 'Configuration Coinbase incomplète'];
            }

            // Convertir le montant FCFA en USD (approximativement)
            $amountInUSD = $this->convertFCFAtoUSD($transaction->amount);

            $payload = [
                'name' => 'Dépôt Winpawa',
                'description' => "Dépôt de {$transaction->amount} FCFA sur Winpawa",
                'pricing_type' => 'fixed_price',
                'local_price' => [
                    'amount' => number_format($amountInUSD, 2, '.', ''),
                    'currency' => $currency,
                ],
                'metadata' => [
                    'transaction_id' => $transaction->id,
                    'transaction_reference' => $transaction->reference,
                    'user_id' => $transaction->user_id,
                    'amount_fcfa' => $transaction->amount,
                ],
                'redirect_url' => config('app.url') . '/payment/success',
                'cancel_url' => config('app.url') . '/payment/cancel',
            ];

            Log::info('Creating Coinbase charge', ['payload' => $payload]);

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/charges", $payload);

            if ($response->successful()) {
                $data = $response->json('data');

                Log::info('Coinbase charge created', ['charge_id' => $data['id'] ?? null]);

                return [
                    'success' => true,
                    'charge_id' => $data['id'],
                    'hosted_url' => $data['hosted_url'],
                    'expires_at' => $data['expires_at'],
                    'pricing' => $data['pricing'],
                    'addresses' => $data['addresses'] ?? [],
                ];
            }

            $errorBody = $response->json();
            Log::error('Coinbase charge creation failed', [
                'status' => $response->status(),
                'error' => $errorBody
            ]);

            return [
                'success' => false,
                'message' => $errorBody['error']['message'] ?? 'Erreur lors de la création du paiement'
            ];

        } catch (\Exception $e) {
            Log::error('Coinbase charge creation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Récupérer les détails d'un charge
     */
    public function getCharge(string $chargeId): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/charges/{$chargeId}");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json('data')];
            }

            return ['success' => false, 'message' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Coinbase get charge error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Vérifier la signature du webhook
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        // Essayer d'abord de récupérer depuis ServiceConfiguration (dashboard)
        $config = ServiceConfiguration::getCoinbaseConfig();
        $webhookSecret = '';

        if ($config && $config->is_active) {
            $webhookSecret = $config->coinbase_webhook_secret ?? '';
        } else {
            // Fallback sur les variables d'environnement
            $webhookSecret = config('services.coinbase.webhook_secret', env('COINBASE_COMMERCE_WEBHOOK_SECRET', ''));
        }

        if (empty($webhookSecret)) {
            Log::warning('Coinbase webhook secret not configured');
            return false;
        }

        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Convertir FCFA en USD (taux approximatif)
     */
    protected function convertFCFAtoUSD(float $amountFCFA): float
    {
        // Taux approximatif : 1 USD ≈ 600 FCFA
        // Vous pouvez utiliser une API de conversion pour un taux en temps réel
        $exchangeRate = 600;
        return $amountFCFA / $exchangeRate;
    }

    /**
     * Obtenir les headers pour les requêtes API
     */
    protected function getHeaders(): array
    {
        return [
            'X-CC-Api-Key' => $this->apiKey,
            'X-CC-Version' => $this->apiVersion,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
