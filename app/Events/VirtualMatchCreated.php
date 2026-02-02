<?php

namespace App\Events;

use App\Models\VirtualMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VirtualMatchCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public VirtualMatch $match;

    /**
     * Create a new event instance.
     */
    public function __construct(VirtualMatch $match)
    {
        $this->match = $match;
        \Log::info('🆕 [Event] VirtualMatchCreated créé', [
            'match_id' => $match->id,
            'reference' => $match->reference,
            'teams' => $match->team_home . ' vs ' . $match->team_away,
            'status' => $match->status->value,
            'starts_at' => $match->starts_at?->toISOString(),
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('virtual-matches'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'match.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $data = [
            'match' => [
                'id' => $this->match->id,
                'reference' => $this->match->reference,
                'team_home' => $this->match->team_home,
                'team_away' => $this->match->team_away,
                'team_home_logo' => $this->match->team_home_logo,
                'team_away_logo' => $this->match->team_away_logo,
                'sport_type' => $this->match->sport_type,
                'league' => $this->match->league,
                'season' => $this->match->season,
                'duration' => $this->match->duration,
                'status' => $this->match->status->value,
                'score_home' => $this->match->score_home,
                'score_away' => $this->match->score_away,
                'score' => $this->match->score,
                'starts_at' => $this->match->starts_at?->toISOString(),
                'ends_at' => $this->match->ends_at?->toISOString(),
                'bet_closure_seconds' => $this->match->bet_closure_seconds,
                'min_bet_amount' => $this->match->min_bet_amount,
                'max_bet_amount' => $this->match->max_bet_amount,
                'available_markets' => $this->match->available_markets,
                'odds' => $this->match->getOdds(), // 🎲 AJOUT DES COTES
                'is_open_for_bets' => $this->match->is_open_for_bets,
            ],
        ];

        \Log::info('📡 [Broadcast] VirtualMatchCreated data', [
            'match_id' => $this->match->id,
            'status' => $this->match->status->value,
        ]);

        return $data;
    }
}
