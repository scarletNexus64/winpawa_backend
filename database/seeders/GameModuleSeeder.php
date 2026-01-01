<?php

namespace Database\Seeders;

use App\Models\GameModule;
use Illuminate\Database\Seeder;

class GameModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Jeux Casino',
                'slug' => 'jeux-casino',
                'icon' => 'heroicon-o-puzzle-piece',
                'description' => 'Jeux de casino classiques : roulette, cartes à gratter, jackpot, etc.',
                'is_locked' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Paris Sportifs',
                'slug' => 'paris-sportifs',
                'icon' => 'heroicon-o-trophy',
                'description' => 'Pariez sur vos sports favoris : football, basketball, tennis, etc.',
                'is_locked' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Jeux de Cartes',
                'slug' => 'jeux-de-cartes',
                'icon' => 'heroicon-o-rectangle-group',
                'description' => 'Poker, Blackjack, Baccarat et autres jeux de cartes.',
                'is_locked' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Loterie',
                'slug' => 'loterie',
                'icon' => 'heroicon-o-ticket',
                'description' => 'Loteries instantanées et tirages quotidiens.',
                'is_locked' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Jeux en Direct',
                'slug' => 'jeux-en-direct',
                'icon' => 'heroicon-o-video-camera',
                'description' => 'Jeux avec croupiers en direct pour une expérience immersive.',
                'is_locked' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Machines à Sous',
                'slug' => 'machines-a-sous',
                'icon' => 'heroicon-o-currency-dollar',
                'description' => 'Des centaines de machines à sous avec jackpots progressifs.',
                'is_locked' => true,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($modules as $module) {
            GameModule::updateOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }
    }
}
