<?php

namespace Database\Seeders;

use App\Models\GameCategory;
use Illuminate\Database\Seeder;

class GameCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Jeux de Chance',
                'slug' => 'jeux-de-chance',
                'icon' => '🎰',
                'color' => 'success',
                'description' => 'Jeux basés sur la chance et le hasard : roulettes, jackpots, cartes à gratter.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Jeux de Prédiction',
                'slug' => 'jeux-de-prediction',
                'icon' => '🔮',
                'color' => 'info',
                'description' => 'Devinez le résultat : pile ou face, dés, nombres chanceux.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Jeux d\'Action',
                'slug' => 'jeux-d-action',
                'icon' => '⚡',
                'color' => 'danger',
                'description' => 'Jeux dynamiques et interactifs : tir au but, pierre-papier-ciseaux.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Jeux de Stratégie',
                'slug' => 'jeux-de-strategie',
                'icon' => '🧠',
                'color' => 'warning',
                'description' => 'Jeux nécessitant réflexion et stratégie : quiz, coffres, ludo.',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            GameCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
