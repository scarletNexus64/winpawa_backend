<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\SportMatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SportSeeder extends Seeder
{
    public function run(): void
    {
        // Catégories de sports
        $realSports = SportCategory::create([
            'name' => 'Sports Réels',
            'slug' => 'sports-reels',
            'icon' => '⚽',
            'color' => 'from-blue-600 to-cyan-500',
            'description' => 'Paris sur des matchs réels',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $virtualSports = SportCategory::create([
            'name' => 'Sports Virtuels',
            'slug' => 'sports-virtuels',
            'icon' => '🎮',
            'color' => 'from-purple-600 to-pink-500',
            'description' => 'Matchs virtuels toutes les 5 minutes',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Sports Réels
        $football = Sport::create([
            'sport_category_id' => $realSports->id,
            'name' => 'Football',
            'slug' => 'football',
            'type' => 'football',
            'icon' => '⚽',
            'description' => 'Paris sur les matchs de football',
            'is_live' => true,
            'is_virtual' => false,
            'is_active' => true,
            'match_duration' => 90,
            'sort_order' => 1,
        ]);

        $basketball = Sport::create([
            'sport_category_id' => $realSports->id,
            'name' => 'Basketball',
            'slug' => 'basketball',
            'type' => 'basketball',
            'icon' => '🏀',
            'description' => 'NBA et autres ligues',
            'is_live' => false,
            'is_virtual' => false,
            'is_active' => true,
            'match_duration' => 48,
            'sort_order' => 2,
        ]);

        $tennis = Sport::create([
            'sport_category_id' => $realSports->id,
            'name' => 'Tennis',
            'slug' => 'tennis',
            'type' => 'tennis',
            'icon' => '🎾',
            'description' => 'Tournois internationaux',
            'is_live' => false,
            'is_virtual' => false,
            'is_active' => true,
            'match_duration' => 180,
            'sort_order' => 3,
        ]);

        // Sports Virtuels
        $virtualFootball = Sport::create([
            'sport_category_id' => $virtualSports->id,
            'name' => 'Football Virtuel',
            'slug' => 'football-virtuel',
            'type' => 'virtual',
            'icon' => '⚡',
            'description' => 'Matchs toutes les 5 minutes',
            'is_live' => true,
            'is_virtual' => true,
            'is_active' => true,
            'match_duration' => 5,
            'sort_order' => 1,
        ]);

        // Matchs de Football
        $this->createFootballMatches($football);
        $this->createBasketballMatches($basketball);
        $this->createTennisMatches($tennis);
        $this->createVirtualMatches($virtualFootball);
    }

    private function createFootballMatches(Sport $sport): void
    {
        $matches = [
            ['Real Madrid', 'Barcelona', 'La Liga', '+2 hours'],
            ['Manchester United', 'Liverpool', 'Premier League', '+4 hours'],
            ['PSG', 'Marseille', 'Ligue 1', '+6 hours'],
            ['Bayern Munich', 'Dortmund', 'Bundesliga', '+8 hours'],
            ['Juventus', 'Milan', 'Serie A', '+1 day'],
        ];

        foreach ($matches as $index => $match) {
            SportMatch::create([
                'sport_id' => $sport->id,
                'home_team' => $match[0],
                'away_team' => $match[1],
                'league' => $match[2],
                'match_time' => now()->modify($match[3]),
                'status' => 'upcoming',
                'odds' => [
                    '1' => rand(15, 35) / 10,
                    'X' => rand(25, 40) / 10,
                    '2' => rand(15, 35) / 10,
                ],
                'is_featured' => $index < 2,
            ]);
        }
    }

    private function createBasketballMatches(Sport $sport): void
    {
        $matches = [
            ['Lakers', 'Warriors', 'NBA', '+1 day'],
            ['Celtics', 'Nets', 'NBA', '+1 day'],
            ['Bucks', 'Heat', 'NBA', '+2 days'],
        ];

        foreach ($matches as $match) {
            SportMatch::create([
                'sport_id' => $sport->id,
                'home_team' => $match[0],
                'away_team' => $match[1],
                'league' => $match[2],
                'match_time' => now()->modify($match[3]),
                'status' => 'upcoming',
                'odds' => [
                    '1' => rand(15, 30) / 10,
                    '2' => rand(15, 30) / 10,
                ],
            ]);
        }
    }

    private function createTennisMatches(Sport $sport): void
    {
        $matches = [
            ['Djokovic', 'Nadal', 'Roland Garros', '+4 hours'],
            ['Federer', 'Alcaraz', 'Wimbledon', '+1 day'],
        ];

        foreach ($matches as $match) {
            SportMatch::create([
                'sport_id' => $sport->id,
                'home_team' => $match[0],
                'away_team' => $match[1],
                'league' => $match[2],
                'match_time' => now()->modify($match[3]),
                'status' => 'upcoming',
                'odds' => [
                    '1' => rand(12, 25) / 10,
                    '2' => rand(12, 25) / 10,
                ],
            ]);
        }
    }

    private function createVirtualMatches(Sport $sport): void
    {
        $teams = [
            'Eagles FC', 'Lions United', 'Tigers SC', 'Panthers FC',
            'Wolves United', 'Hawks SC', 'Dragons FC', 'Phoenix United'
        ];

        for ($i = 0; $i < 10; $i++) {
            $homeIndex = rand(0, count($teams) - 1);
            $awayIndex = rand(0, count($teams) - 1);
            while ($awayIndex === $homeIndex) {
                $awayIndex = rand(0, count($teams) - 1);
            }

            SportMatch::create([
                'sport_id' => $sport->id,
                'home_team' => $teams[$homeIndex],
                'away_team' => $teams[$awayIndex],
                'league' => 'Virtual League',
                'match_time' => now()->addMinutes(5 * ($i + 1)),
                'status' => 'upcoming',
                'odds' => [
                    '1' => rand(15, 35) / 10,
                    'X' => rand(25, 35) / 10,
                    '2' => rand(15, 35) / 10,
                ],
                'is_featured' => $i < 3,
            ]);
        }
    }
}
