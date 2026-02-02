<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Payment\CoinbaseService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected CoinbaseService $coinbaseService
    ) {}

    public function initiateDeposit(Transaction $transaction, string $phone, string $method): array
    {
        return match ($method) {
            'mtn_momo' => $this->initiateMtnDeposit($transaction, $phone),
            'orange_money' => $this->initiateOrangeDeposit($transaction, $phone),
            default => ['success' => false, 'message' => 'Méthode de paiement non supportée'],
        };
    }

    /**
     * Initier un dépôt via Coinbase Commerce
     */
    public function initiateCoinbaseDeposit(Transaction $transaction): array
    {
        try {
            $result = $this->coinbaseService->createCharge($transaction);

            if (!$result['success']) {
                return $result;
            }

            return [
                'success' => true,
                'charge_id' => $result['charge_id'],
                'hosted_url' => $result['hosted_url'],
                'expires_at' => $result['expires_at'],
                'coinbase_data' => $result,
            ];

        } catch (\Exception $e) {
            Log::error('Coinbase deposit initiation failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création du paiement Coinbase: ' . $e->getMessage()
            ];
        }
    }

    protected function initiateMtnDeposit(Transaction $transaction, string $phone): array
    {
        $config = config('winpawa.mtn_momo');

        if ($config['environment'] === 'sandbox') {
            // Mode sandbox - simuler le succès
            return [
                'success' => true,
                'reference' => 'MTN-' . strtoupper(uniqid()),
                'message' => 'Paiement initié (sandbox)',
            ];
        }

        try {
            // API MTN MoMo réelle
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getMtnToken(),
                'X-Reference-Id' => $transaction->reference,
                'X-Target-Environment' => $config['environment'],
                'Ocp-Apim-Subscription-Key' => $config['subscription_key'],
            ])->post('https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay', [
                'amount' => (string) $transaction->amount,
                'currency' => 'XAF',
                'externalId' => $transaction->reference,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $phone,
                ],
                'payerMessage' => 'Dépôt WINPAWA',
                'payeeNote' => 'Dépôt sur compte WINPAWA',
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'reference' => $transaction->reference,
                    'message' => 'Paiement initié',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur MTN MoMo: ' . $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('MTN MoMo Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion MTN MoMo',
            ];
        }
    }

    protected function initiateOrangeDeposit(Transaction $transaction, string $phone): array
    {
        $config = config('winpawa.orange_money');

        if ($config['environment'] === 'sandbox') {
            return [
                'success' => true,
                'reference' => 'OM-' . strtoupper(uniqid()),
                'message' => 'Paiement initié (sandbox)',
            ];
        }

        try {
            // API Orange Money réelle
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getOrangeToken(),
                'Content-Type' => 'application/json',
            ])->post('https://api.orange.com/orange-money-webpay/cm/v1/webpayment', [
                'merchant_key' => $config['merchant_key'],
                'currency' => 'XAF',
                'order_id' => $transaction->reference,
                'amount' => $transaction->amount,
                'return_url' => config('app.url') . '/payment/callback',
                'cancel_url' => config('app.url') . '/payment/cancel',
                'notif_url' => config('app.url') . '/api/webhooks/orange-money',
                'lang' => 'fr',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'reference' => $data['pay_token'] ?? $transaction->reference,
                    'payment_url' => $data['payment_url'] ?? null,
                    'message' => 'Paiement initié',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur Orange Money',
            ];

        } catch (\Exception $e) {
            Log::error('Orange Money Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion Orange Money',
            ];
        }
    }

    public function processWithdrawal(Transaction $transaction, string $phone, string $method): array
    {
        return match ($method) {
            'mtn_momo' => $this->processMtnWithdrawal($transaction, $phone),
            'orange_money' => $this->processOrangeWithdrawal($transaction, $phone),
            default => ['success' => false, 'message' => 'Méthode non supportée'],
        };
    }

    protected function processMtnWithdrawal(Transaction $transaction, string $phone): array
    {
        $config = config('winpawa.mtn_momo');

        if ($config['environment'] === 'sandbox') {
            return [
                'success' => true,
                'reference' => 'MTN-OUT-' . strtoupper(uniqid()),
            ];
        }

        // Implémentation réelle du disbursement MTN
        // ...

        return ['success' => true, 'reference' => $transaction->reference];
    }

    protected function processOrangeWithdrawal(Transaction $transaction, string $phone): array
    {
        $config = config('winpawa.orange_money');

        if ($config['environment'] === 'sandbox') {
            return [
                'success' => true,
                'reference' => 'OM-OUT-' . strtoupper(uniqid()),
            ];
        }

        // Implémentation réelle du cashout Orange Money
        // ...

        return ['success' => true, 'reference' => $transaction->reference];
    }

    protected function getMtnToken(): string
    {
        // Implémentation OAuth MTN
        return 'mtn_access_token';
    }

    protected function getOrangeToken(): string
    {
        // Implémentation OAuth Orange
        return 'orange_access_token';
    }
}
