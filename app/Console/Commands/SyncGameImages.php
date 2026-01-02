<?php

namespace App\Console\Commands;

use App\Enums\GameType;
use App\Models\Game;
use Illuminate\Console\Command;

class SyncGameImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:sync-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les images des jeux avec les fichiers disponibles dans public/images';

    /**
     * Mapping des types de jeux avec les noms d'images
     */
    private array $imageMapping = [
        'roulette' => 'applefortune.png',
        'scratch_card' => 'cartegratter.png',
        'coin_flip' => 'pileface.png',
        'dice' => 'lancede.png',
        'rock_paper_scissors' => 'pierrepapierciseau.png',
        'treasure_box' => 'coffretresor.png',
        'lucky_number' => 'nombrechance.png',
        'jackpot' => 'jackpot.png',
        'penalty' => 'tiraubut.png',
        'ludo' => 'ludosimple.png',
        'quiz' => 'quizzchance.png',
        'color_roulette' => 'roulettecouleur.png',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Synchronisation des images des jeux...');
        $this->newLine();

        $games = Game::all();
        $updatedCount = 0;

        foreach ($games as $game) {
            $typeValue = $game->type->value;

            if (isset($this->imageMapping[$typeValue])) {
                $imagePath = $this->imageMapping[$typeValue];
                $fullPath = public_path('images/' . $imagePath);

                if (file_exists($fullPath)) {
                    $game->update(['image' => $imagePath]);
                    $this->line("✅ {$game->name} → {$this->imageMapping[$typeValue]}");
                    $updatedCount++;
                } else {
                    $this->warn("⚠️  {$game->name} → Image non trouvée: {$imagePath}");
                }
            } else {
                $this->error("❌ {$game->name} → Aucun mapping trouvé pour le type: {$typeValue}");
            }
        }

        $this->newLine();
        $this->info("✨ Synchronisation terminée! {$updatedCount} jeu(x) mis à jour.");

        return Command::SUCCESS;
    }
}
