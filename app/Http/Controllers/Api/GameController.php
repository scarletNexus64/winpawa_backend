<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Bet;
use App\Services\RngService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function __construct(
        protected RngService $rngService
    ) {}

    public function index(): JsonResponse
    {
        // Retourner TOUS les jeux (actifs et inactifs)
        // Le frontend affichera un cadenas pour les jeux inactifs/non configurés
        $games = Game::ordered()
            ->get()
            ->map(fn ($game) => $this->formatGame($game));

        return response()->json([
            'success' => true,
            'data' => $games,
        ]);
    }

    public function featured(): JsonResponse
    {
        $games = Game::active()
            ->featured()
            ->ordered()
            ->limit(6)
            ->get()
            ->map(fn ($game) => $this->formatGame($game));

        return response()->json([
            'success' => true,
            'data' => $games,
        ]);
    }

    public function show(Game $game): JsonResponse
    {
        // Retourner les données du jeu même s'il est inactif
        // Le frontend gérera l'affichage du cadenas
        return response()->json([
            'success' => true,
            'data' => $this->formatGame($game, true),
        ]);
    }

    public function play(Request $request, Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Ce jeu n\'est pas disponible.',
            ], 400);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:' . $game->min_bet, 'max:' . $game->max_bet],
            'choice' => ['required', 'string'],
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        // Vérifier le solde
        if (!$wallet->canDebit($validated['amount'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Solde avant
            \Log::info('💰 Wallet AVANT:', [
                'main' => $wallet->main_balance,
                'bonus' => $wallet->bonus_balance,
                'total' => $wallet->total_balance,
            ]);

            // Débiter le wallet
            $wallet->debit($validated['amount'], 'bet');
            \Log::info('💸 Après DÉBIT:', [
                'amount' => $validated['amount'],
                'total' => $wallet->fresh()->total_balance,
            ]);

            // Créer le pari
            $bet = Bet::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'amount' => $validated['amount'],
                'choice' => $validated['choice'],
                'status' => 'pending',
            ]);

            // Générer le résultat avec le RNG
            $result = $this->rngService->generateResult($game, $bet);
            \Log::info('🎲 Résultat RNG:', $result);

            // Mettre à jour le pari
            $bet->update([
                'result' => $result['result'],
                'is_winner' => $result['is_winner'],
                'multiplier' => $result['multiplier'],
                'payout' => $result['payout'],
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Créditer si gagnant
            if ($result['is_winner']) {
                $wallet->credit($result['payout'], 'win');
                \Log::info('✅ Après CRÉDIT:', [
                    'payout' => $result['payout'],
                    'total' => $wallet->fresh()->total_balance,
                ]);
            } else {
                \Log::info('❌ PERTE - Pas de crédit');
            }

            // Mettre à jour le wagering du bonus si applicable
            $activeBonus = $user->bonuses()->where('status', 'active')->first();
            if ($activeBonus) {
                $activeBonus->addWager($validated['amount']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $result['is_winner'] ? 'Félicitations ! Vous avez gagné !' : 'Pas de chance, réessayez !',
                'data' => [
                    'bet' => [
                        'id' => $bet->id,
                        'reference' => $bet->reference,
                        'amount' => (float) $bet->amount,
                        'choice' => $bet->choice,
                        'result' => $bet->result,
                        'is_winner' => $bet->is_winner,
                        'multiplier' => (float) $bet->multiplier,
                        'payout' => (float) $bet->payout,
                    ],
                    'wallet' => [
                        'main_balance' => (float) $wallet->fresh()->main_balance,
                        'bonus_balance' => (float) $wallet->fresh()->bonus_balance,
                        'total_balance' => (float) $wallet->fresh()->total_balance,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Réessayez.',
            ], 500);
        }
    }

    public function history(Request $request): JsonResponse
    {
        $bets = Bet::where('user_id', $request->user()->id)
            ->with('game:id,name,slug,type')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bets->items(),
            'meta' => [
                'current_page' => $bets->currentPage(),
                'last_page' => $bets->lastPage(),
                'per_page' => $bets->perPage(),
                'total' => $bets->total(),
            ],
        ]);
    }

    protected function formatGame(Game $game, bool $withDetails = false): array
    {
        $data = [
            'id' => $game->id,
            'name' => $game->name,
            'slug' => $game->slug,
            'type' => $game->type,
            'description' => $game->description,
            'thumbnail' => $game->image ? url('images/' . $game->image) : null,
            'banner' => $game->banner ? url('storage/' . $game->banner) : null,
            'rtp' => (float) $game->rtp,
            'win_frequency' => (float) $game->win_frequency,
            'min_bet' => (float) $game->min_bet,
            'max_bet' => (float) $game->max_bet,
            'multipliers' => $game->multipliers,
            'is_featured' => $game->is_featured,
            'is_active' => $game->is_active,
            'is_configured' => $game->is_configured,
        ];

        if ($withDetails) {
            $data['settings'] = $game->settings;
        }

        return $data;
    }
}
