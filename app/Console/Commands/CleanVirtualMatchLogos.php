<?php

namespace App\Console\Commands;

use App\Models\VirtualMatch;
use Illuminate\Console\Command;

class CleanVirtualMatchLogos extends Command
{
    protected $signature = 'virtual-match:clean-logos';
    protected $description = 'Nettoie les anciens chemins de logos invalides (teams/...)';

    public function handle()
    {
        $this->info('🧹 Nettoyage des anciens chemins de logos...');

        $matches = VirtualMatch::where(function ($query) {
            $query->where('team_home_logo', 'like', 'teams/%')
                  ->orWhere('team_away_logo', 'like', 'teams/%');
        })->get();

        if ($matches->isEmpty()) {
            $this->comment('✅ Aucun match à nettoyer');
            return Command::SUCCESS;
        }

        $this->info("Trouvé {$matches->count()} match(s) avec des chemins invalides");

        foreach ($matches as $match) {
            $cleaned = false;

            if ($match->team_home_logo && str_starts_with($match->team_home_logo, 'teams/')) {
                $match->team_home_logo = null;
                $cleaned = true;
            }

            if ($match->team_away_logo && str_starts_with($match->team_away_logo, 'teams/')) {
                $match->team_away_logo = null;
                $cleaned = true;
            }

            if ($cleaned) {
                $match->save();
                $this->line("✓ Nettoyé : {$match->team_home} vs {$match->team_away}");
            }
        }

        $this->newLine();
        $this->info('✅ Nettoyage terminé !');
        $this->comment('💡 Vous pouvez maintenant éditer les matchs et sélectionner les bons logos');

        return Command::SUCCESS;
    }
}
