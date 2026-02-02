<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\UserBonus;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function balance(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet;

        return response()->json([
            'success' => true,
            'data' => [
                'main_balance' => (float) $wallet->main_balance,
                'bonus_balance' => (float) $wallet->bonus_balance,
                'affiliate_balance' => (float) $wallet->affiliate_balance,
                'total_balance' => (float) $wallet->total_balance,
                'withdrawable_balance' => (float) $wallet->withdrawable_balance,
                'currency' => $wallet->currency,
                'is_locked' => $wallet->is_locked,
            ],
        ]);
    }

    public function deposit(Request $request): JsonResponse
    {
        $minDeposit = config('winpawa.deposit.minimum', 200);
        $maxDeposit = config('winpawa.deposit.maximum', 1000000);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:' . $minDeposit, 'max:' . $maxDeposit],
            'payment_method' => ['required', 'in:mtn_momo,orange_money,coinbase'],
            'phone' => ['required_if:payment_method,mtn_momo,orange_money', 'string', 'nullable'],
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        try {
            DB::beginTransaction();

            // Créer la transaction en pending
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => TransactionType::DEPOSIT,
                'amount' => $validated['amount'],
                'fee' => 0,
                'net_amount' => $validated['amount'],
                'balance_before' => $wallet->main_balance,
                'balance_after' => $wallet->main_balance, // Sera mis à jour après confirmation
                'status' => TransactionStatus::PENDING,
                'payment_method' => $validated['payment_method'],
                'description' => 'Dépôt via ' . match ($validated['payment_method']) {
                    'mtn_momo' => 'MTN Mobile Money',
                    'orange_money' => 'Orange Money',
                    'coinbase' => 'Coinbase Commerce (Crypto)',
                    default => $validated['payment_method'],
                },
                'metadata' => [
                    'phone' => $validated['phone'] ?? null,
                ],
            ]);

            // Traitement selon la méthode de paiement
            if ($validated['payment_method'] === 'coinbase') {
                $paymentResult = $this->paymentService->initiateCoinbaseDeposit($transaction);
            } else {
                // Mobile Money (MTN/Orange)
                $paymentResult = $this->paymentService->initiateDeposit(
                    $transaction,
                    $validated['phone'],
                    $validated['payment_method']
                );
            }

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }

            $transaction->update([
                'payment_reference' => $paymentResult['reference'] ?? $paymentResult['charge_id'] ?? null,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'coinbase_data' => $paymentResult['coinbase_data'] ?? null,
                ]),
            ]);

            DB::commit();

            // Réponse différente pour Coinbase (avec URL de paiement)
            if ($validated['payment_method'] === 'coinbase') {
                return response()->json([
                    'success' => true,
                    'message' => 'Page de paiement Coinbase générée.',
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'reference' => $transaction->reference,
                        'amount' => (float) $transaction->amount,
                        'status' => 'pending',
                        'payment_url' => $paymentResult['hosted_url'],
                        'expires_at' => $paymentResult['expires_at'] ?? null,
                        'charge_id' => $paymentResult['charge_id'] ?? null,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dépôt initié. Confirmez le paiement sur votre téléphone.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => (float) $transaction->amount,
                    'status' => 'pending',
                    'payment_reference' => $paymentResult['reference'],
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du dépôt: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function withdraw(Request $request): JsonResponse
    {
        $minWithdrawal = config('winpawa.withdrawal.minimum', 1000);
        $maxWithdrawal = config('winpawa.withdrawal.maximum', 500000);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:' . $minWithdrawal, 'max:' . $maxWithdrawal],
            'payment_method' => ['required', 'in:mtn_momo,orange_money'],
            'phone' => ['required', 'string'],
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        // Vérifications
        if (!$user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez vérifier votre compte avant de retirer.',
            ], 400);
        }

        if ($wallet->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Votre portefeuille est temporairement bloqué.',
            ], 400);
        }

        if ($wallet->withdrawable_balance < $validated['amount']) {
            return response()->json([
                'success' => false,
                'message' => 'Solde retirable insuffisant.',
            ], 400);
        }

        // Vérifier les conditions de wagering
        if (!$user->hasMetWageringRequirements()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez remplir les conditions de mise avant de retirer.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Débiter immédiatement
            $wallet->main_balance -= $validated['amount'];
            $wallet->save();

            // Créer la transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => TransactionType::WITHDRAWAL,
                'amount' => $validated['amount'],
                'fee' => 0,
                'net_amount' => $validated['amount'],
                'balance_before' => $wallet->main_balance + $validated['amount'],
                'balance_after' => $wallet->main_balance,
                'status' => TransactionStatus::PENDING,
                'payment_method' => $validated['payment_method'],
                'description' => 'Retrait via ' . ($validated['payment_method'] === 'mtn_momo' ? 'MTN Mobile Money' : 'Orange Money'),
                'metadata' => [
                    'phone' => $validated['phone'],
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Demande de retrait soumise. Traitement sous 24h.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => (float) $transaction->amount,
                    'status' => 'pending',
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait.',
            ], 500);
        }
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions->map(fn ($t) => [
                'id' => $t->id,
                'reference' => $t->reference,
                'type' => $t->type->value,
                'type_label' => $t->type->label(),
                'amount' => (float) $t->amount,
                'status' => $t->status->value,
                'status_label' => $t->status->label(),
                'payment_method' => $t->payment_method,
                'created_at' => $t->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    public function claimBonus(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $pendingBonus = $user->bonuses()
            ->where('status', 'pending')
            ->first();

        if (!$pendingBonus) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun bonus en attente.',
            ], 400);
        }

        $pendingBonus->activate();

        return response()->json([
            'success' => true,
            'message' => 'Bonus activé ! ' . number_format($pendingBonus->amount, 0) . ' FCFA ajoutés.',
            'data' => [
                'bonus_amount' => (float) $pendingBonus->amount,
                'wagering_requirement' => (float) $pendingBonus->wagering_requirement,
                'wallet' => [
                    'main_balance' => (float) $user->wallet->fresh()->main_balance,
                    'bonus_balance' => (float) $user->wallet->fresh()->bonus_balance,
                ],
            ],
        ]);
    }
}
