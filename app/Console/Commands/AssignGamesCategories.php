<?php

namespace App\Console\Commands;

use App\Enums\GameType;
use App\Models\Game;
use App\Models\GameCategory;
use Illuminate\Console\Command;

class AssignGamesCategories extends Command
{
    protected $signature = 'games:assign-categories';
    protected $description = 'Assigner les catégories aux jeux existants';

    public function handle()
    {
        $this->info('Attribution des catégories aux jeux...');

        // Récupérer les catégories
        $categories = [
            'jeux-de-chance' => GameCategory::where('slug', 'jeux-de-chance')->first(),
            'jeux-de-prediction' => GameCategory::where('slug', 'jeux-de-prediction')->first(),
            'jeux-d-action' => GameCategory::where('slug', 'jeux-d-action')->first(),
            'jeux-de-strategie' => GameCategory::where('slug', 'jeux-de-strategie')->first(),
        ];

        // Vérifier que toutes les catégories existent
        foreach ($categories as $slug => $category) {
            if (!$category) {
                $this->error("La catégorie {$slug} n'existe pas. Veuillez exécuter GameCategorySeeder.");
                return 1;
            }
        }

        // Mapper les types de jeux aux catégories
        $mapping = [
            // Jeux de Chance
            GameType::ROULETTE->value => $categories['jeux-de-chance']->id,
            GameType::SCRATCH_CARD->value => $categories['jeux-de-chance']->id,
            GameType::JACKPOT->value => $categories['jeux-de-chance']->id,

            // Jeux de Prédiction
            GameType::COIN_FLIP->value => $categories['jeux-de-prediction']->id,
            GameType::DICE->value => $categories['jeux-de-prediction']->id,
            GameType::LUCKY_NUMBER->value => $categories['jeux-de-prediction']->id,
            GameType::COLOR_ROULETTE->value => $categories['jeux-de-prediction']->id,

            // Jeux d'Action
            GameType::PENALTY->value => $categories['jeux-d-action']->id,
            GameType::ROCK_PAPER_SCISSORS->value => $categories['jeux-d-action']->id,

            // Jeux de Stratégie
            GameType::TREASURE_BOX->value => $categories['jeux-de-strategie']->id,
            GameType::LUDO->value => $categories['jeux-de-strategie']->id,
            GameType::QUIZ->value => $categories['jeux-de-strategie']->id,
        ];

        $updated = 0;
        $skipped = 0;

        // Assigner les catégories aux jeux
        foreach ($mapping as $gameType => $categoryId) {
            $gamesUpdated = Game::where('type', $gameType)
                ->whereNull('category_id')
                ->update(['category_id' => $categoryId]);

            $updated += $gamesUpdated;

            $categoryName = array_search($categoryId, array_column($categories, 'id', 'slug'));
            $this->info("Type {$gameType}: {$gamesUpdated} jeux assignés");
        }

        // Compter les jeux déjà assignés
        $alreadyAssigned = Game::whereNotNull('category_id')->count();

        $this->newLine();
        $this->info("✅ Attribution terminée !");
        $this->info("📊 {$updated} jeux ont été assignés à une catégorie");
        $this->info("✔️  {$alreadyAssigned} jeux ont déjà une catégorie");

        return 0;
    }
}
