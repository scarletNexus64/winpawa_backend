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

        $minBet = $virtualMatch->min_bet_amount ?? 100;
        $maxBet = $virtualMatch->max_bet_amount ?? 100000;

        $validated = $request->validate([
            'bet_type' => ['required', 'in:result,score,both_score,both_teams_score,double_chance,over_under,exact_score,first_half,handicap'],
            'choice' => ['required', 'string'],
            'amount' => ['required', 'numeric', "min:$minBet", "max:$maxBet"],
        ]);

        // Vérifier si le type de pari est disponible pour ce match
        $availableMarkets = $virtualMatch->available_markets ?? ['result', 'both_teams_score', 'over_under'];

        // Mapping des anciens types vers les nouveaux
        $betTypeMapping = [
            'result' => 'result',
            'score' => 'exact_score',
            'both_score' => 'both_teams_score',
            'both_teams_score' => 'both_teams_score',
            'double_chance' => 'double_chance',
            'over_under' => 'over_under',
            'exact_score' => 'exact_score',
            'first_half' => 'first_half',
            'handicap' => 'handicap',
        ];

        $marketKey = $betTypeMapping[$validated['bet_type']] ?? $validated['bet_type'];

        if (!in_array($marketKey, $availableMarkets)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce type de pari n\'est pas disponible pour ce match.',
            ], 400);
        }

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

    public function myHistory(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Paramètres de filtrage
        $myBetsOnly = $request->boolean('my_bets_only', false);
        $search = $request->input('search');
        $perPage = min($request->input('per_page', 20), 50);

        // Requête de base - tous les matchs terminés
        $query = VirtualMatch::completed();

        // Filtrer par recherche (équipe ou référence)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('team_home', 'like', "%{$search}%")
                  ->orWhere('team_away', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        // Si "mes paris seulement", filtrer les matchs où l'utilisateur a parié
        if ($myBetsOnly) {
            $query->whereHas('bets', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        // Charger les paris de l'utilisateur (même si on affiche tous les matchs)
        $query->with(['bets' => function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orderBy('created_at', 'desc');
        }]);

        $matches = $query->orderBy('ends_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $matches->map(function ($match) {
                $userBets = $match->bets;
                $hasBets = $userBets->count() > 0;

                // Calculer le résumé uniquement si l'utilisateur a des paris
                $summary = null;
                if ($hasBets) {
                    $totalStaked = $userBets->sum('amount');
                    $totalPayout = $userBets->sum('payout');
                    $hasWon = $userBets->some('is_winner', true);
                    $netResult = $totalPayout - $totalStaked;

                    $summary = [
                        'total_staked' => (float) $totalStaked,
                        'total_payout' => (float) $totalPayout,
                        'net_result' => (float) $netResult,
                        'has_won' => $hasWon,
                        'bets_count' => $userBets->count(),
                    ];
                }

                return [
                    'match' => $this->formatMatch($match),
                    'has_bets' => $hasBets,
                    'bets' => $hasBets ? $userBets->map(fn ($bet) => [
                        'id' => $bet->id,
                        'reference' => $bet->reference,
                        'bet_type' => $bet->bet_type,
                        'choice' => $bet->choice,
                        'amount' => (float) $bet->amount,
                        'multiplier' => (float) $bet->multiplier,
                        'payout' => (float) $bet->payout,
                        'is_winner' => $bet->is_winner,
                        'status' => $bet->status,
                        'created_at' => $bet->created_at->toISOString(),
                    ]) : [],
                    'summary' => $summary,
                ];
            }),
            'meta' => [
                'current_page' => $matches->currentPage(),
                'last_page' => $matches->lastPage(),
                'total' => $matches->total(),
                'per_page' => $matches->perPage(),
            ],
        ]);
    }

    /**
     * Get user's bets for a specific match
     */
    public function getMatchBets(Request $request, VirtualMatch $virtualMatch): JsonResponse
    {
        $bets = VirtualMatchBet::where('user_id', $request->user()->id)
            ->where('virtual_match_id', $virtualMatch->id)
            ->where('status', 'pending') // Seulement les paris actifs (pas encore réglés)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bets->map(fn ($bet) => [
                'id' => $bet->id,
                'reference' => $bet->reference,
                'bet_type' => $bet->bet_type,
                'choice' => $bet->choice,
                'amount' => (float) $bet->amount,
                'multiplier' => (float) $bet->multiplier,
                'potential_win' => (float) ($bet->amount * $bet->multiplier),
                'status' => $bet->status,
                'can_modify' => $virtualMatch->is_open_for_bets, // Peut modifier si paris ouverts
                'created_at' => $bet->created_at->toISOString(),
            ]),
        ]);
    }

    /**
     * Update an existing bet (only if match hasn't started)
     */
    public function updateBet(Request $request, VirtualMatchBet $bet): JsonResponse
    {
        // Vérifier que c'est bien le pari de l'utilisateur
        if ($bet->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        // Vérifier que le match n'a pas commencé
        $match = $bet->virtualMatch;
        if (!$match->is_open_for_bets) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier ce pari, les paris sont fermés.',
            ], 400);
        }

        // Vérifier que le pari est encore en attente
        if ($bet->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier un pari déjà réglé.',
            ], 400);
        }

        $minBet = $match->min_bet_amount ?? 100;
        $maxBet = $match->max_bet_amount ?? 100000;

        $validated = $request->validate([
            'bet_type' => ['sometimes', 'in:result,score,both_score,both_teams_score,double_chance,over_under,exact_score,first_half,handicap'],
            'choice' => ['sometimes', 'string'],
            'amount' => ['sometimes', 'numeric', "min:$minBet", "max:$maxBet"],
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();
            $wallet = $user->wallet;
            $oldAmount = $bet->amount;
            $newAmount = $validated['amount'] ?? $oldAmount;

            // Si le montant change, ajuster le wallet
            if ($newAmount != $oldAmount) {
                $difference = $newAmount - $oldAmount;

                if ($difference > 0) {
                    // Augmentation : débiter la différence
                    if (!$wallet->canDebit($difference)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Solde insuffisant pour cette modification.',
                        ], 400);
                    }
                    $wallet->debit($difference, 'virtual_match_bet_update');
                } else {
                    // Diminution : créditer la différence
                    $wallet->credit(abs($difference), 'virtual_match_bet_refund');
                }
            }

            // Recalculer le multiplicateur si le type ou le choix change
            $betType = $validated['bet_type'] ?? $bet->bet_type;
            $choice = $validated['choice'] ?? $bet->choice;

            $multipliers = VirtualMatchBet::getMultipliers();
            $multiplier = $multipliers[$choice] ?? $multipliers[$betType] ?? $bet->multiplier;

            // Mettre à jour le pari
            $bet->update([
                'bet_type' => $betType,
                'choice' => $choice,
                'amount' => $newAmount,
                'multiplier' => $multiplier,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pari modifié avec succès !',
                'data' => [
                    'bet' => [
                        'id' => $bet->id,
                        'reference' => $bet->reference,
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
                'message' => 'Erreur lors de la modification du pari.',
            ], 500);
        }
    }

    /**
     * Delete a bet (only if match hasn't started)
     */
    public function deleteBet(Request $request, VirtualMatchBet $bet): JsonResponse
    {
        // Vérifier que c'est bien le pari de l'utilisateur
        if ($bet->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        // Vérifier que le match n'a pas commencé
        $match = $bet->virtualMatch;
        if (!$match->is_open_for_bets) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'annuler ce pari, les paris sont fermés.',
            ], 400);
        }

        // Vérifier que le pari est encore en attente
        if ($bet->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'annuler un pari déjà réglé.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            $wallet = $user->wallet;

            // Rembourser le montant du pari
            $wallet->credit($bet->amount, 'virtual_match_bet_cancelled');

            // Supprimer le pari
            $bet->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pari annulé et remboursé avec succès !',
                'data' => [
                    'refunded_amount' => (float) $bet->amount,
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
                'message' => 'Erreur lors de l\'annulation du pari.',
            ], 500);
        }
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
            'league' => $match->league,
            'season' => $match->season,
            'duration' => $match->duration,
            'status' => $match->status->value,
            'status_label' => $match->status->label(),
            'score' => $match->score,
            'score_home' => $match->score_home ?? 0,
            'score_away' => $match->score_away ?? 0,
            'result' => $match->result,
            'starts_at' => $match->starts_at?->toISOString(),
            'ends_at' => $match->ends_at?->toISOString(),
            'countdown' => $match->countdown,
            'is_open_for_bets' => $match->is_open_for_bets,
            'bet_closure_seconds' => $match->bet_closure_seconds ?? 5,
            'min_bet_amount' => (float) ($match->min_bet_amount ?? 100),
            'max_bet_amount' => (float) ($match->max_bet_amount ?? 100000),
            'available_markets' => $match->available_markets ?? ['result', 'both_teams_score', 'over_under'],
            'odds' => $match->getOdds(), // Cotes personnalisées ou par défaut
            'match_events' => $match->match_events ?? [],
        ];
    }
}
