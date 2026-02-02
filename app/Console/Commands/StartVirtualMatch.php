<?php

namespace App\Console\Commands;

use App\Enums\VirtualMatchStatus;
use App\Jobs\SimulateVirtualMatch;
use App\Models\VirtualMatch;
use Illuminate\Console\Command;

class StartVirtualMatch extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'virtual-match:start {matchId?}';

    /**
     * The console command description.
     */
    protected $description = 'Démarre un match virtuel et simule les événements en temps réel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $matchId = $this->argument('matchId');

        if ($matchId) {
            $match = VirtualMatch::find($matchId);

            if (!$match) {
                $this->error("Match #{$matchId} introuvable.");
                return 1;
            }

            if ($match->status !== VirtualMatchStatus::UPCOMING) {
                $this->error("Le match doit être en statut UPCOMING. Statut actuel : {$match->status->value}");
                return 1;
            }
        } else {
            // Chercher un match UPCOMING qui commence bientôt
            $match = VirtualMatch::where('status', VirtualMatchStatus::UPCOMING)
                ->orderBy('starts_at', 'asc')
                ->first();

            if (!$match) {
                $this->error('Aucun match UPCOMING trouvé.');
                return 1;
            }
        }

        $this->info("Démarrage du match : {$match->team_home} vs {$match->team_away}");
        $this->info("Référence : {$match->reference}");
        $this->info("Durée : {$match->duration} minutes");

        // Démarrer le match
        $match->start();
        $this->info('✓ Match démarré (statut LIVE)');

        // Lancer le job de simulation avec les événements pré-configurés
        $eventsCount = count($match->match_events ?? []);
        $this->info("Simulation du match avec {$eventsCount} événement(s) configuré(s)...");
        $this->info('Les événements seront broadcastés via WebSocket.');

        // Dispatch le job de manière synchrone pour la démo
        SimulateVirtualMatch::dispatchSync($match);

        $this->info('✓ Match terminé !');
        $this->info("Score final : {$match->score_home} - {$match->score_away}");

        return 0;
    }
}
