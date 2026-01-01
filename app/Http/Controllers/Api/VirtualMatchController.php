<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VirtualMatch;
use App\Models\VirtualMatchBet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VirtualMatchController extends Controller
{
    public function upcoming(): JsonResponse
    {
        $matches = VirtualMatch::upcoming()
            ->orderBy('starts_at')
            ->limit(10)
            ->get()
            ->map(fn ($m) => $this->formatMatch($m));

        return response()->json([
            'success' => true,
            'data' => $matches,
        ]);
    }

    public function live(): JsonResponse
    {
        $matches = VirtualMatch::live()
            ->get()
            ->map(fn ($m) => $this->formatMatch($m));

        return response()->json([
            'success' => true,
            'data' => $matches,
        ]);
    }

    public function results(): JsonResponse
    {
        $matches = VirtualMatch::completed()
            ->orderBy('ends_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($m) => $this->formatMatch($m));

        return response()->json([
            'success' => true,
            'data' => $matches,
        ]);
    }

    public function placeBet(Request $request, VirtualMatch $virtualMatch): JsonResponse
    {
        if (!$virtualMatch->is_open_for_bets) {
            return response()->json([
                'success' => false,
                'message' => 'Les paris sont fermés pour ce match.',
            ], 400);
        }

        $validated = $request->validate([
            'bet_type' => ['required', 'in:result,score,both_score'],
            'choice' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100', 'max:100000'],
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet->canDebit($validated['amount'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant.',
            ], 400);
        }

        $multipliers = VirtualMatchBet::getMultipliers();
        $multiplier = $multipliers[$validated['choice']] ?? $multipliers[$validated['bet_type']] ?? 2.0;

        try {
            DB::beginTransaction();

            $wallet->debit($validated['amount'], 'virtual_match_bet');

            $bet = VirtualMatchBet::create([
                'user_id' => $user->id,
                'virtual_match_id' => $virtualMatch->id,
                'bet_type' => $validated['bet_type'],
                'choice' => $validated['choice'],
                'amount' => $validated['amount'],
                'multiplier' => $multiplier,
                'status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pari placé avec succès !',
                'data' => [
                    'bet' => [
                        'id' => $bet->id,
                        'reference' => $bet->reference,
                        'match' => $this->formatMatch($virtualMatch),
                        'bet_type' => $bet->bet_type,
                        'choice' => $bet->choice,
                        'amount' => (float) $bet->amount,
                        'multiplier' => (float) $bet->multiplier,
                        'potential_win' => (float) ($bet->amount * $bet->multiplier),
                    ],
                    'wallet' => [
                        'main_balance' => (float) $wallet->fresh()->main_balance,
                        'total_balance' => (float) $wallet->fresh()->total_balance,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du placement du pari.',
            ], 500);
        }
    }

    public function myBets(Request $request): JsonResponse
    {
        $bets = VirtualMatchBet::where('user_id', $request->user()->id)
            ->with('virtualMatch:id,reference,team_home,team_away,status,score_home,score_away,result')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bets->map(fn ($bet) => [
                'id' => $bet->id,
                'reference' => $bet->reference,
                'match' => [
                    'reference' => $bet->virtualMatch->reference,
                    'teams' => $bet->virtualMatch->team_home . ' vs ' . $bet->virtualMatch->team_away,
                    'status' => $bet->virtualMatch->status,
                    'score' => $bet->virtualMatch->score,
                    'result' => $bet->virtualMatch->result,
                ],
                'bet_type' => $bet->bet_type,
                'choice' => $bet->choice,
                'amount' => (float) $bet->amount,
                'multiplier' => (float) $bet->multiplier,
                'payout' => (float) $bet->payout,
                'is_winner' => $bet->is_winner,
                'status' => $bet->status,
                'created_at' => $bet->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $bets->currentPage(),
                'last_page' => $bets->lastPage(),
                'total' => $bets->total(),
            ],
        ]);
    }

    protected function formatMatch(VirtualMatch $match): array
    {
        return [
            'id' => $match->id,
            'reference' => $match->reference,
            'team_home' => $match->team_home,
            'team_away' => $match->team_away,
            'team_home_logo' => $match->team_home_logo,
            'team_away_logo' => $match->team_away_logo,
            'sport_type' => $match->sport_type,
            'duration' => $match->duration,
            'status' => $match->status->value,
            'status_label' => $match->status->label(),
            'score' => $match->score,
            'result' => $match->result,
            'starts_at' => $match->starts_at?->toISOString(),
            'countdown' => $match->countdown,
            'is_open_for_bets' => $match->is_open_for_bets,
            'multipliers' => VirtualMatchBet::getMultipliers(),
        ];
    }
}
