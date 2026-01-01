<?php

namespace Database\Seeders;

use App\Enums\GameType;
use App\Models\Game;
use App\Models\GameModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer le module "Jeux Casino"
        $casinoModule = GameModule::where('slug', 'jeux-casino')->first();

        if (!$casinoModule) {
            $this->command->error('Le module "Jeux Casino" n\'existe pas. Veuillez exécuter GameModuleSeeder d\'abord.');
            return;
        }
        $games = [
            [
                'name' => 'Apple of Fortune',
                'type' => GameType::ROULETTE,
                'description' => 'Faites tourner la roue et gagnez jusqu\'à 10x votre mise !',
                'rtp' => 80,
                'win_frequency' => 40,
                'multipliers' => [2, 5, 10],
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Cartes à Gratter',
                'type' => GameType::SCRATCH_CARD,
                'description' => 'Grattez et découvrez vos gains instantanés.',
                'rtp' => 75,
                'win_frequency' => 35,
                'multipliers' => [2, 3, 5, 10],
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pile ou Face',
                'type' => GameType::COIN_FLIP,
                'description' => 'Le classique ! Pile ou Face, doublez votre mise.',
                'rtp' => 77.5,
                'win_frequency' => 50,
                'multipliers' => [2],
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Lancer de Dés',
                'type' => GameType::DICE,
                'description' => 'Pariez sur le nombre ou pair/impair.',
                'rtp' => 77.5,
                'win_frequency' => 40,
                'multipliers' => [2, 3],
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Pierre-Papier-Ciseaux',
                'type' => GameType::ROCK_PAPER_SCISSORS,
                'description' => 'Battez l\'ordinateur et doublez votre mise !',
                'rtp' => 77.5,
                'win_frequency' => 33,
                'multipliers' => [2],
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Coffre au Trésor',
                'type' => GameType::TREASURE_BOX,
                'description' => 'Choisissez le bon coffre et gagnez gros !',
                'rtp' => 77.5,
                'win_frequency' => 30,
                'multipliers' => [2, 3, 5],
                'is_featured' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Nombre Chanceux',
                'type' => GameType::LUCKY_NUMBER,
                'description' => 'Devinez le nombre entre 1 et 10.',
                'rtp' => 77.5,
                'win_frequency' => 20,
                'multipliers' => [2, 3],
                'is_featured' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Jackpot',
                'type' => GameType::JACKPOT,
                'description' => 'Tentez votre chance au jackpot !',
                'rtp' => 75,
                'win_frequency' => 25,
                'multipliers' => [2, 3, 5, 10],
                'is_featured' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Tir au But',
                'type' => GameType::PENALTY,
                'description' => 'Marquez et gagnez ! Le gardien ou le tireur ?',
                'rtp' => 77.5,
                'win_frequency' => 40,
                'multipliers' => [2, 3, 5],
                'is_featured' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Course de Pions',
                'type' => GameType::LUDO,
                'description' => 'Pariez sur le pion gagnant.',
                'rtp' => 77.5,
                'win_frequency' => 35,
                'multipliers' => [2, 3, 5],
                'is_featured' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'Quiz Chance',
                'type' => GameType::QUIZ,
                'description' => 'Répondez correctement et doublez votre mise.',
                'rtp' => 77.5,
                'win_frequency' => 50,
                'multipliers' => [2],
                'is_featured' => false,
                'sort_order' => 11,
            ],
            [
                'name' => 'Roulette Couleurs',
                'type' => GameType::COLOR_ROULETTE,
                'description' => 'Rouge, Bleu, Vert ou Jaune ? À vous de choisir !',
                'rtp' => 77.5,
                'win_frequency' => 33,
                'multipliers' => [2, 3],
                'is_featured' => false,
                'sort_order' => 12,
            ],
        ];

        foreach ($games as $gameData) {
            $type = $gameData['type'];
            unset($gameData['type']);

            // Générer les noms de fichiers des images en fonction du type
            $typeValue = $type->value;
            $thumbnailPath = "games/thumbnails/{$typeValue}-thumbnail.png";
            $bannerPath = "games/banners/{$typeValue}-banner.png";

            Game::create([
                ...$gameData,
                'module_id' => $casinoModule->id,
                'slug' => Str::slug($gameData['name']),
                'type' => $type->value,
                'thumbnail' => $thumbnailPath,
                'banner' => $bannerPath,
                'min_bet' => config('winpawa.betting.default_min', 100),
                'max_bet' => config('winpawa.betting.default_max', 100000),
                'is_active' => true,
                'settings' => Game::getDefaultSettings($type),
            ]);
        }
    }
}
