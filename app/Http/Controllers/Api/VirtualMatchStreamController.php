<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VirtualMatch;
use App\Enums\VirtualMatchStatus;
use Illuminate\Http\Request;

class VirtualMatchStreamController extends Controller
{
    /**
     * Stream live updates of virtual matches using Server-Sent Events (SSE)
     */
    public function stream(Request $request)
    {
        return response()->stream(function () {
            // Disable output buffering and ignore client disconnect
            if (ob_get_level()) ob_end_clean();
            ignore_user_abort(true);
            set_time_limit(0);

            $lastUpdateHash = null;
            $isFirstUpdate = true;

            while (true) {
                // Get current matches
                $upcoming = VirtualMatch::where('status', VirtualMatchStatus::UPCOMING)
                    ->where('starts_at', '>', now())
                    ->orderBy('starts_at')
                    ->take(20)
                    ->get();

                $live = VirtualMatch::where('status', VirtualMatchStatus::LIVE)
                    ->orderBy('starts_at', 'desc')
                    ->take(10)
                    ->get();

                // Create hash of current state to detect changes
                $currentHash = md5(json_encode([
                    'upcoming' => $upcoming->pluck(['id', 'status', 'score_home', 'score_away'])->toArray(),
                    'live' => $live->pluck(['id', 'status', 'score_home', 'score_away'])->toArray(),
                ]));

                // Send update if something changed or if it's the first update
                if ($isFirstUpdate || $currentHash !== $lastUpdateHash) {
                    $data = [
                        'upcoming' => $upcoming->map(function ($match) {
                            return $this->formatMatch($match);
                        }),
                        'live' => $live->map(function ($match) {
                            return $this->formatMatch($match);
                        }),
                        'timestamp' => now()->toISOString(),
                    ];

                    echo "data: " . json_encode($data) . "\n\n";
                    ob_flush();
                    flush();

                    $lastUpdateHash = $currentHash;
                    $isFirstUpdate = false;
                }

                // Wait 3 seconds before next check
                sleep(3);

                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Nginx compatibility
        ]);
    }

    protected function formatMatch(VirtualMatch $match): array
    {
        return [
            'id' => $match->id,
            'reference' => $match->reference,
            'team_home' => $match->team_home,
            'team_away' => $match->team_away,
            'team_home_logo' => $match->team_home_logo ? 'images/' . $match->team_home_logo : null,
            'team_away_logo' => $match->team_away_logo ? 'images/' . $match->team_away_logo : null,
            'sport_type' => $match->sport_type,
            'league' => $match->league,
            'season' => $match->season,
            'duration' => $match->duration,
            'status' => $match->status->value,
            'status_label' => $match->status->label(),
            'score' => $match->score,
            'result' => $match->result,
            'starts_at' => $match->starts_at?->toISOString(),
            'countdown' => $match->countdown,
            'is_open_for_bets' => $match->is_open_for_bets,
            'bet_closure_seconds' => $match->bet_closure_seconds ?? 5,
            'min_bet_amount' => (float) ($match->min_bet_amount ?? 100),
            'max_bet_amount' => (float) ($match->max_bet_amount ?? 100000),
            'available_markets' => $match->available_markets ?? ['result', 'both_teams_score', 'over_under'],
            'multipliers' => \App\Models\VirtualMatchBet::getMultipliers(),
        ];
    }
}
