<?php

namespace App\Console\Commands;

use App\Services\GameImageGenerator;
use Illuminate\Console\Command;

class GenerateGameImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:generate-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les bannières et vignettes par défaut pour tous les types de jeux';

    /**
     * Execute the console command.
     */
    public function handle(GameImageGenerator $generator)
    {
        $this->info('🎨 Génération des images pour les jeux...');
        $this->newLine();

        try {
            $generated = $generator->generateAllImages();

            $this->info('✅ Images générées avec succès :');
            $this->newLine();

            foreach ($generated as $game) {
                $this->line("  🎮 {$game['name']}");
                $this->line("     📸 Vignette: {$game['thumbnail']}");
                $this->line("     🖼️  Bannière: {$game['banner']}");
                $this->newLine();
            }

            $this->info("✨ Total: " . count($generated) . " jeux traités");

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la génération des images:');
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
