<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use App\Services\Payment\CoinbaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoinbaseWebhookController extends Controller
{
    public function __construct(
        protected CoinbaseService $coinbaseService
    ) {}

    /**
     * Gérer les webhooks de Coinbase Commerce
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Récupérer la signature du webhook
            $signature = $request->header('X-CC-Webhook-Signature');
            $payload = $request->getContent();

            // Vérifier la signature
            if (!$this->coinbaseService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Coinbase webhook signature verification failed');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $event = json_decode($payload, true);

            Log::info('Coinbase webhook received', [
                'event_type' => $event['event']['type'] ?? 'unknown',
                'event_id' => $event['event']['id'] ?? null,
            ]);

            // Traiter l'événement selon son type
            $eventType = $event['event']['type'] ?? null;
            $chargeData = $event['event']['data'] ?? null;

            if (!$chargeData) {
                Log::error('Coinbase webhook: missing charge data');
                return response()->json(['error' => 'Missing data'], 400);
            }

            $result = match ($eventType) {
                'charge:created' => $this->handleChargeCreated($chargeData),
                'charge:confirmed' => $this->handleChargeConfirmed($chargeData),
                'charge:failed' => $this->handleChargeFailed($chargeData),
                'charge:delayed' => $this->handleChargeDelayed($chargeData),
                'charge:pending' => $this->handleChargePending($chargeData),
                'charge:resolved' => $this->handleChargeResolved($chargeData),
                default => $this->handleUnknownEvent($eventType),
            };

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Coinbase webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Charge créé (paiement initié)
     */
    protected function handleChargeCreated(array $chargeData): array
    {
        Log::info('Coinbase charge created', ['charge_id' => $chargeData['id']]);

        return ['status' => 'acknowledged'];
    }

    /**
     * Charge confirmé (paiement reçu et confirmé)
     */
    protected function handleChargeConfirmed(array $chargeData): array
    {
        DB::beginTransaction();

        try {
            $chargeId = $chargeData['id'];
            $metadata = $chargeData['metadata'] ?? [];
            $transactionId = $metadata['transaction_id'] ?? null;

            if (!$transactionId) {
                Log::error('Coinbase charge confirmed but no transaction_id in metadata', [
                    'charge_id' => $chargeId
                ]);
                return ['status' => 'error', 'message' => 'Missing transaction_id'];
            }

            $transaction = Transaction::find($transactionId);

            if (!$transaction) {
                Log::error('Transaction not found', ['transaction_id' => $transactionId]);
                return ['status' => 'error', 'message' => 'Transaction not found'];
            }

            if ($transaction->status === TransactionStatus::COMPLETED) {
                Log::info('Transaction already completed', ['transaction_id' => $transactionId]);
                return ['status' => 'already_processed'];
            }

            // Créditer le wallet
            $wallet = $transaction->wallet;
            $wallet->main_balance += $transaction->amount;
            $wallet->save();

            // Mettre à jour la transaction
            $transaction->status = TransactionStatus::COMPLETED;
            $transaction->balance_after = $wallet->main_balance;
            $transaction->completed_at = now();
            $transaction->metadata = array_merge($transaction->metadata ?? [], [
                'coinbase_charge' => $chargeData,
                'confirmed_at' => now()->toISOString(),
            ]);
            $transaction->save();

            Log::info('Coinbase payment confirmed and wallet credited', [
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'new_balance' => $wallet->main_balance,
            ]);

            DB::commit();

            return ['status' => 'success', 'transaction_id' => $transaction->id];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing confirmed charge', [
                'error' => $e->getMessage(),
                'charge_id' => $chargeData['id'] ?? null,
            ]);

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Charge échoué (paiement échoué)
     */
    protected function handleChargeFailed(array $chargeData): array
    {
        try {
            $metadata = $chargeData['metadata'] ?? [];
            $transactionId = $metadata['transaction_id'] ?? null;

            if ($transactionId) {
                $transaction = Transaction::find($transactionId);

                if ($transaction) {
                    $transaction->status = TransactionStatus::FAILED;
                    $transaction->metadata = array_merge($transaction->metadata ?? [], [
                        'failure_reason' => 'Coinbase charge failed',
                        'failed_at' => now()->toISOString(),
                    ]);
                    $transaction->save();

                    Log::info('Coinbase payment failed', [
                        'transaction_id' => $transaction->id,
                    ]);
                }
            }

            return ['status' => 'acknowledged'];

        } catch (\Exception $e) {
            Log::error('Error processing failed charge', [
                'error' => $e->getMessage(),
            ]);

            return ['status' => 'error'];
        }
    }

    /**
     * Charge en attente
     */
    protected function handleChargePending(array $chargeData): array
    {
        Log::info('Coinbase charge pending', ['charge_id' => $chargeData['id']]);

        return ['status' => 'acknowledged'];
    }

    /**
     * Charge retardé (besoin de confirmations supplémentaires)
     */
    protected function handleChargeDelayed(array $chargeData): array
    {
        Log::info('Coinbase charge delayed', ['charge_id' => $chargeData['id']]);

        return ['status' => 'acknowledged'];
    }

    /**
     * Charge résolu (paiement résolu après retard)
     */
    protected function handleChargeResolved(array $chargeData): array
    {
        // Traiter comme une confirmation
        return $this->handleChargeConfirmed($chargeData);
    }

    /**
     * Événement inconnu
     */
    protected function handleUnknownEvent(?string $eventType): array
    {
        Log::warning('Unknown Coinbase webhook event', ['event_type' => $eventType]);

        return ['status' => 'unknown_event'];
    }
}
