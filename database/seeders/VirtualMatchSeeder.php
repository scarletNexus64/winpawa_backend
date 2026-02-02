<?php

namespace Database\Seeders;

use App\Models\VirtualMatch;
use App\Enums\VirtualMatchStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VirtualMatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎮 Création de 4 matchs virtuels de démonstration avec logos...');

        // ==================== MATCH 1 : LIVE (En direct) ====================
        $this->command->info('📍 Match 1 : Real Madrid vs Barcelona (EN DIRECT)');

        VirtualMatch::create([
            'team_home' => 'Real Madrid',
            'team_away' => 'FC Barcelona',
            'team_home_logo' => 'logo_equipe/real.png',
            'team_away_logo' => 'logo_equipe/barca.png',
            'sport_type' => 'football',
            'league' => 'La Liga',
            'season' => '2024/2025',
            'duration' => 3,
            'status' => VirtualMatchStatus::LIVE,

            // Configuration des paris
            'bet_closure_seconds' => 5,
            'min_bet_amount' => 100,
            'max_bet_amount' => 100000,
            'available_markets' => ['result', 'both_teams_score', 'over_under', 'exact_score'],

            // Scénario : Real Madrid gagne 2-1
            'result' => 'home_win',
            'score_home' => 2,
            'score_away' => 1,
            'score_first_half_home' => 1,
            'score_first_half_away' => 0,
            'score_second_half_home' => 1,
            'score_second_half_away' => 1,

            // Événements du match
            'match_events' => [
                [
                    'event_type' => 'goal',
                    'team' => 'home',
                    'minute' => 23,
                    'player' => 'Vinicius Jr',
                ],
                [
                    'event_type' => 'yellow_card',
                    'team' => 'away',
                    'minute' => 38,
                    'player' => 'Gavi',
                ],
                [
                    'event_type' => 'goal',
                    'team' => 'home',
                    'minute' => 67,
                    'player' => 'Benzema',
                ],
                [
                    'event_type' => 'goal',
                    'team' => 'away',
                    'minute' => 78,
                    'player' => 'Lewandowski',
                ],
                [
                    'event_type' => 'red_card',
                    'team' => 'away',
                    'minute' => 85,
                    'player' => 'Sergio Busquets',
                ],
            ],

            'starts_at' => now()->subMinutes(2),
            'ends_at' => null,
            'rng_seed' => hash('sha256', uniqid(mt_rand(), true)),
        ]);

        // ==================== MATCH 2 : À VENIR (Dans 5 minutes) ====================
        $this->command->info('📍 Match 2 : Bayern Munich vs Borussia Dortmund (Dans 5 min)');

        VirtualMatch::create([
            'team_home' => 'Bayern Munich',
            'team_away' => 'Borussia Dortmund',
            'team_home_logo' => 'logo_equipe/bayern.png',
            'team_away_logo' => 'logo_equipe/dormund.png',
            'sport_type' => 'football',
            'league' => 'Bundesliga',
            'season' => '2024/2025',
            'duration' => 5,
            'status' => VirtualMatchStatus::UPCOMING,

            // Configuration des paris
            'bet_closure_seconds' => 10,
            'min_bet_amount' => 50,
            'max_bet_amount' => 50000,
            'available_markets' => ['result', 'double_chance', 'both_teams_score', 'over_under', 'first_half'],

            // Match à venir - scores à 0
            'score_home' => 0,
            'score_away' => 0,
            'match_events' => [],

            'starts_at' => now()->addMinutes(5),
            'ends_at' => null,
            'rng_seed' => hash('sha256', uniqid(mt_rand(), true)),
        ]);

        // ==================== MATCH 3 : À VENIR (Dans 10 minutes) ====================
        $this->command->info('📍 Match 3 : Chelsea vs Tottenham (Dans 10 min)');

        VirtualMatch::create([
            'team_home' => 'Chelsea FC',
            'team_away' => 'Tottenham Hotspur',
            'team_home_logo' => 'logo_equipe/chelsea.png',
            'team_away_logo' => 'logo_equipe/totenam.png',
            'sport_type' => 'football',
            'league' => 'Premier League',
            'season' => '2024/2025',
            'duration' => 3,
            'status' => VirtualMatchStatus::UPCOMING,

            // Configuration des paris
            'bet_closure_seconds' => 5,
            'min_bet_amount' => 100,
            'max_bet_amount' => 200000,
            'available_markets' => ['result', 'over_under', 'exact_score', 'handicap'],

            // Match à venir - scores à 0
            'score_home' => 0,
            'score_away' => 0,
            'match_events' => [],

            'starts_at' => now()->addMinutes(10),
            'ends_at' => null,
            'rng_seed' => hash('sha256', uniqid(mt_rand(), true)),
        ]);

        // ==================== MATCH 4 : À VENIR (Dans 15 minutes) ====================
        $this->command->info('📍 Match 4 : Atletico Madrid vs Real Madrid (Dans 15 min)');

        VirtualMatch::create([
            'team_home' => 'Atletico Madrid',
            'team_away' => 'Real Madrid',
            'team_home_logo' => 'logo_equipe/altelico.png',
            'team_away_logo' => 'logo_equipe/real.png',
            'sport_type' => 'football',
            'league' => 'La Liga',
            'season' => '2024/2025',
            'duration' => 1,
            'status' => VirtualMatchStatus::UPCOMING,

            // Configuration des paris
            'bet_closure_seconds' => 3,
            'min_bet_amount' => 200,
            'max_bet_amount' => 150000,
            'available_markets' => ['result', 'both_teams_score', 'first_half'],

            // Match à venir - scores à 0
            'score_home' => 0,
            'score_away' => 0,
            'match_events' => [],

            'starts_at' => now()->addMinutes(15),
            'ends_at' => null,
            'rng_seed' => hash('sha256', uniqid(mt_rand(), true)),
        ]);

        $this->command->info('');
        $this->command->info('✅ 4 matchs virtuels créés avec succès !');
        $this->command->info('   📺 1 match EN DIRECT : Real Madrid vs Barcelona');
        $this->command->info('   ⏰ 3 matchs À VENIR :');
        $this->command->info('      - Bayern vs Dortmund (5 min)');
        $this->command->info('      - Chelsea vs Tottenham (10 min)');
        $this->command->info('      - Atletico vs Real Madrid (15 min)');
        $this->command->info('');
        $this->command->info('🎯 Configurations variées :');
        $this->command->info('   - Durées : 1, 3, 5 minutes');
        $this->command->info('   - Ligues : La Liga, Bundesliga, Premier League');
        $this->command->info('   - Marchés de paris différents');
        $this->command->info('   - Limites de mise variées');
        $this->command->info('   - Événements détaillés (buts, cartons, corners, etc.)');
    }
}
