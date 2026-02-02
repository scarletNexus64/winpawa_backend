<?php

namespace App\Console\Commands;

use App\Models\VirtualMatch;
use App\Enums\VirtualMatchStatus;
use App\Jobs\SimulateVirtualMatch;
use Illuminate\Console\Command;

class UpdateVirtualMatchStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'virtual-match:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour automatiquement les statuts des matchs virtuels (upcoming → live → completed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Mise à jour des statuts des matchs virtuels...');

        // ==================== 1. Passer les matchs "À venir" en "Live" ====================
        $upcomingMatches = VirtualMatch::where('status', VirtualMatchStatus::UPCOMING)
            ->where('starts_at', '<=', now())
            ->get();

        foreach ($upcomingMatches as $match) {
            // Utiliser la méthode start() pour initialiser correctement le match
            $match->start();

            // Dispatcher le job de simulation du match (utilise les événements pré-configurés)
            SimulateVirtualMatch::dispatch($match);

            $this->line("▶️  Match démarré : {$match->team_home} vs {$match->team_away} (simulation lancée)");
        }

        if ($upcomingMatches->count() > 0) {
            $this->info("✅ {$upcomingMatches->count()} match(s) passé(s) en LIVE");
        }

        // ==================== 2. Passer les matchs "Live" en "Completed" ====================
        // Note: La complétion est normalement gérée par le job SimulateVirtualMatch
        // Ce code sert de filet de sécurité pour les cas où le job échoue
        $liveMatches = VirtualMatch::where('status', VirtualMatchStatus::LIVE)
            ->whereNotNull('starts_at')
            ->get();

        $completedCount = 0;

        foreach ($liveMatches as $match) {
            // Calculer la fin du match : starts_at + duration (en minutes)
            // On ajoute 2 minutes de marge pour laisser le job SimulateVirtualMatch terminer
            $expectedEndTime = $match->starts_at->copy()->addMinutes($match->duration + 2);

            // Si le temps est écoulé + marge de sécurité, terminer le match
            // Cela ne devrait arriver que si le job SimulateVirtualMatch a échoué
            if (now()->greaterThanOrEqualTo($expectedEndTime)) {
                $match->complete();

                $this->line("🏁 Match terminé (fallback) : {$match->team_home} vs {$match->team_away} - Score: {$match->score}");
                $completedCount++;
            }
        }

        if ($completedCount > 0) {
            $this->info("✅ {$completedCount} match(s) terminé(s)");
        }

        // ==================== Résumé ====================
        if ($upcomingMatches->count() === 0 && $completedCount === 0) {
            $this->comment('ℹ️  Aucun match à mettre à jour');
        }

        $this->newLine();
        $this->info('📊 Statut actuel :');
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['À venir', VirtualMatch::where('status', VirtualMatchStatus::UPCOMING)->count()],
                ['En direct', VirtualMatch::where('status', VirtualMatchStatus::LIVE)->count()],
                ['Terminés', VirtualMatch::where('status', VirtualMatchStatus::COMPLETED)->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
