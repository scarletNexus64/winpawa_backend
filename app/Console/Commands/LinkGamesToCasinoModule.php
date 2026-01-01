<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\GameModule;
use Illuminate\Console\Command;

class LinkGamesToCasinoModule extends Command
{
    protected $signature = 'games:link-to-casino';
    protected $description = 'Link existing games to the Casino module';

    public function handle()
    {
        $casinoModule = GameModule::where('slug', 'jeux-casino')->first();

        if (!$casinoModule) {
            $this->error('Module Casino non trouvé. Veuillez exécuter le GameModuleSeeder d\'abord.');
            return 1;
        }

        $updated = Game::whereNull('module_id')->update(['module_id' => $casinoModule->id]);

        $this->info("Mis à jour {$updated} jeux avec le module Casino.");

        return 0;
    }
}
