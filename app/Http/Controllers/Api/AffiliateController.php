<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $user->affiliateStats;

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'referral_link' => config('app.frontend_url') . '/register?ref=' . $user->referral_code,
                'total_referrals' => $stats?->total_referrals ?? 0,
                'active_referrals' => $stats?->active_referrals ?? 0,
                'total_deposits' => (float) ($stats?->total_deposits_from_referrals ?? 0),
                'total_commission' => (float) ($stats?->total_commission_earned ?? 0),
                'pending_commission' => (float) ($stats?->pending_commission ?? 0),
                'paid_commission' => (float) ($stats?->total_commission_paid ?? 0),
                'affiliate_balance' => (float) $user->wallet->affiliate_balance,
                'min_withdrawal' => config('winpawa.affiliate.min_withdrawal', 5000),
                'deposit_rate' => config('winpawa.affiliate.deposit_commission', 5),
                'loss_rate' => config('winpawa.affiliate.loss_commission', 25),
            ],
        ]);
    }

    public function referrals(Request $request): JsonResponse
    {
        $referrals = $request->user()->referrals()
            ->select('id', 'name', 'created_at')
            ->withCount('bets')
            ->withSum('transactions as total_deposits', 'amount')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $referrals->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'bets_count' => $r->bets_count,
                'total_deposits' => (float) ($r->total_deposits ?? 0),
                'joined_at' => $r->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $referrals->currentPage(),
                'last_page' => $referrals->lastPage(),
                'total' => $referrals->total(),
            ],
        ]);
    }

    public function commissions(Request $request): JsonResponse
    {
        $commissions = AffiliateCommission::where('referrer_id', $request->user()->id)
            ->with('referral:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $commissions->map(fn ($c) => [
                'id' => $c->id,
                'referral_name' => $c->referral?->name ?? 'Utilisateur',
                'type' => $c->type,
                'type_label' => match($c->type) {
                    'deposit' => 'Commission dépôt',
                    'loss' => 'Commission perte',
                    'virtual_match_loss' => 'Commission Virtual Match',
                    default => $c->type,
                },
                'amount' => (float) $c->amount,
                'rate' => (float) $c->rate,
                'is_paid' => $c->is_paid,
                'created_at' => $c->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $commissions->currentPage(),
                'last_page' => $commissions->lastPage(),
                'total' => $commissions->total(),
            ],
        ]);
    }

    public function withdraw(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $user->wallet;
        $minWithdrawal = config('winpawa.affiliate.min_withdrawal', 5000);

        if ($wallet->affiliate_balance < $minWithdrawal) {
            return response()->json([
                'success' => false,
                'message' => "Le minimum de retrait est de {$minWithdrawal} FCFA.",
            ], 400);
        }

        // Transférer vers le solde principal
        $amount = $wallet->affiliate_balance;
        $wallet->transferAffiliateToMain();

        // Mettre à jour les stats
        $user->affiliateStats?->payCommission($amount);

        return response()->json([
            'success' => true,
            'message' => number_format($amount, 0) . ' FCFA transférés vers votre solde principal.',
            'data' => [
                'amount' => (float) $amount,
                'wallet' => [
                    'main_balance' => (float) $wallet->fresh()->main_balance,
                    'affiliate_balance' => (float) $wallet->fresh()->affiliate_balance,
                ],
            ],
        ]);
    }
}
