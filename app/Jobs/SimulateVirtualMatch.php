<?php

namespace App\Jobs;

use App\Models\VirtualMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SimulateVirtualMatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public VirtualMatch $match;

    /**
     * Le timeout du job en secondes.
     * Pour un match de 5 min = 300s + marge de 60s = 360s = 6 minutes
     * Pour un match de 1 min = 60s + marge = 120s = 2 minutes
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes (largement suffisant pour tous les matchs)

    /**
     * Create a new job instance.
     */
    public function __construct(VirtualMatch $match)
    {
        $this->match = $match;
    }

    /**
     * Execute the job.
     * Cette méthode simule le match en temps réel en utilisant les événements pré-configurés
     */
    public function handle(): void
    {
        Log::info('🎮 [SimulateVirtualMatch] Démarrage de la simulation', [
            'match_id' => $this->match->id,
            'reference' => $this->match->reference,
            'duration' => $this->match->duration,
        ]);

        // Durée totale du match en secondes
        $durationInSeconds = $this->match->duration * 60;

        // Récupérer les événements pré-configurés
        $configuredEvents = collect($this->match->match_events ?? [])
            ->sortBy('minute')
            ->values()
            ->all();

        Log::info('📋 [SimulateVirtualMatch] Événements configurés', [
            'count' => count($configuredEvents),
            'events' => $configuredEvents,
        ]);

        // Calculer le temps réel par minute virtuelle
        // Par exemple : si durée = 5min, alors 1 minute virtuelle = 60 secondes réelles
        $secondsPerMinute = $durationInSeconds / $this->match->duration;

        $homeScore = 0;
        $awayScore = 0;
        $eventIndex = 0;

        // Simuler minute par minute
        for ($currentMinute = 1; $currentMinute <= $this->match->duration; $currentMinute++) {
            Log::info("⏱️ [SimulateVirtualMatch] Minute {$currentMinute}/{$this->match->duration}");

            // Attendre le temps équivalent à 1 minute virtuelle
            sleep((int) $secondsPerMinute);

            // Rafraîchir le match depuis la DB
            $this->match->refresh();

            // Vérifier s'il y a des événements à cette minute
            while ($eventIndex < count($configuredEvents) &&
                   $configuredEvents[$eventIndex]['minute'] == $currentMinute) {

                $event = $configuredEvents[$eventIndex];
                $eventType = $event['event_type'] ?? 'goal';
                $team = $event['team'] ?? 'home';
                $player = $event['player'] ?? null;

                Log::info("🎯 [SimulateVirtualMatch] Événement détecté", [
                    'minute' => $currentMinute,
                    'type' => $eventType,
                    'team' => $team,
                    'player' => $player,
                ]);

                $matchEvent = null;

                // Traiter l'événement selon son type
                switch ($eventType) {
                    case 'goal':
                    case 'penalty': // Un penalty qui marque est aussi un but
                        if ($team === 'home') {
                            $homeScore++;
                            $teamName = $this->match->team_home;
                        } else {
                            $awayScore++;
                            $teamName = $this->match->team_away;
                        }

                        $matchEvent = [
                            'type' => $eventType,
                            'minute' => $currentMinute,
                            'team' => $team,
                            'team_name' => $teamName,
                            'player' => $player ?? $this->getRandomPlayerName(),
                            'timestamp' => now()->toISOString(),
                        ];

                        Log::info("⚽ [SimulateVirtualMatch] BUT ! Score: {$homeScore}-{$awayScore}");
                        break;

                    case 'yellow_card':
                    case 'red_card':
                        $teamName = $team === 'home' ? $this->match->team_home : $this->match->team_away;

                        $matchEvent = [
                            'type' => $eventType,
                            'minute' => $currentMinute,
                            'team' => $team,
                            'team_name' => $teamName,
                            'player' => $player ?? $this->getRandomPlayerName(),
                            'timestamp' => now()->toISOString(),
                        ];
                        break;

                    case 'corner':
                    case 'offside':
                        $teamName = $team === 'home' ? $this->match->team_home : $this->match->team_away;

                        $matchEvent = [
                            'type' => $eventType,
                            'minute' => $currentMinute,
                            'team' => $team,
                            'team_name' => $teamName,
                            'timestamp' => now()->toISOString(),
                        ];
                        break;
                }

                // Mettre à jour le score et broadcaster
                if ($matchEvent) {
                    $this->match->updateScore($homeScore, $awayScore, $matchEvent);
                    Log::info("📡 [SimulateVirtualMatch] Événement broadcasté", [
                        'score' => "{$homeScore}-{$awayScore}",
                        'event' => $matchEvent,
                    ]);
                }

                $eventIndex++;
            }

            // Vérifier si on est à la mi-temps
            $halfTime = $this->match->duration / 2;
            if ($currentMinute == (int) $halfTime) {
                Log::info("⏸️ [SimulateVirtualMatch] MI-TEMPS ! Score: {$homeScore}-{$awayScore}");

                // Enregistrer les scores de mi-temps
                $this->match->score_first_half_home = $homeScore;
                $this->match->score_first_half_away = $awayScore;
                $this->match->save();

                // Broadcaster un événement de mi-temps
                $halfTimeEvent = [
                    'type' => 'half_time',
                    'minute' => $currentMinute,
                    'score_home' => $homeScore,
                    'score_away' => $awayScore,
                    'timestamp' => now()->toISOString(),
                ];

                event(new \App\Events\VirtualMatchUpdated($this->match, $halfTimeEvent));
            }
        }

        // Rafraîchir une dernière fois
        $this->match->refresh();

        // Enregistrer les scores de deuxième mi-temps
        $this->match->score_second_half_home = $homeScore - ($this->match->score_first_half_home ?? 0);
        $this->match->score_second_half_away = $awayScore - ($this->match->score_first_half_away ?? 0);

        // S'assurer que les scores finaux sont corrects
        $this->match->score_home = $homeScore;
        $this->match->score_away = $awayScore;

        Log::info("🏁 [SimulateVirtualMatch] FIN DU MATCH ! Score final: {$homeScore}-{$awayScore}");

        // Compléter le match
        $this->match->complete();
    }

    /**
     * Générer un nom de joueur aléatoire (fallback si pas de joueur configuré)
     */
    protected function getRandomPlayerName(): string
    {
        $firstNames = ['Jean', 'Pierre', 'Marc', 'Luc', 'Paul', 'David', 'Michel', 'François', 'Emmanuel', 'Joseph'];
        $lastNames = ['Dupont', 'Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Petit', 'Durand', 'Leroy'];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
}
