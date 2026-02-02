<?php

namespace App\Console\Commands;

use App\Models\VirtualMatch;
use Illuminate\Console\Command;

class FixVirtualMatchLogoPaths extends Command
{
    protected $signature = 'virtual-match:fix-logo-paths';
    protected $description = 'Corrige les chemins des logos pour le disque game_images';

    public function handle()
    {
        $this->info('🔧 Correction des chemins de logos...');

        $matches = VirtualMatch::whereNotNull('team_home_logo')
            ->orWhereNotNull('team_away_logo')
            ->get();

        if ($matches->isEmpty()) {
            $this->comment('✅ Aucun match avec logo à corriger');
            return Command::SUCCESS;
        }

        $this->info("Trouvé {$matches->count()} match(s) avec des logos");

        foreach ($matches as $match) {
            $updated = false;

            // Corriger logo domicile
            if ($match->team_home_logo) {
                // Si le chemin commence par 'images/', l'enlever
                if (str_starts_with($match->getRawOriginal('team_home_logo'), 'images/')) {
                    $match->team_home_logo = substr($match->getRawOriginal('team_home_logo'), 7);
                    $updated = true;
                }
            }

            // Corriger logo extérieur
            if ($match->team_away_logo) {
                // Si le chemin commence par 'images/', l'enlever
                if (str_starts_with($match->getRawOriginal('team_away_logo'), 'images/')) {
                    $match->team_away_logo = substr($match->getRawOriginal('team_away_logo'), 7);
                    $updated = true;
                }
            }

            if ($updated) {
                $match->saveQuietly();
                $this->line("✓ Corrigé : {$match->team_home} vs {$match->team_away}");
            }
        }

        $this->newLine();
        $this->info('✅ Correction terminée !');

        return Command::SUCCESS;
    }
}
