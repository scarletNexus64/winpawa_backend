<?php

namespace App\Events;

use App\Models\VirtualMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VirtualMatchCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public VirtualMatch $match;

    /**
     * Create a new event instance.
     */
    public function __construct(VirtualMatch $match)
    {
        $this->match = $match;
        \Log::info('🏁 [Event] VirtualMatchCompleted créé', [
            'match_id' => $match->id,
            'reference' => $match->reference,
            'final_score' => $match->score_home . '-' . $match->score_away,
            'result' => $match->result,
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('virtual-matches'),
            new Channel('virtual-match.' . $this->match->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'match.completed';
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
                'score_first_half_home' => $this->match->score_first_half_home,
                'score_first_half_away' => $this->match->score_first_half_away,
                'result' => $this->match->result,
                'starts_at' => $this->match->starts_at?->toISOString(),
                'ends_at' => $this->match->ends_at?->toISOString(),
                'match_events' => $this->match->match_events,
            ],
        ];

        \Log::info('📡 [Broadcast] VirtualMatchCompleted data', $data);

        return $data;
    }
}
