<?php

namespace App\Services;

use App\Models\DemoSimulation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DemoSimulationService
{
    /**
     * Générer les données de simulation basées sur le scénario
     */
    public function generateSimulationData(DemoSimulation $simulation): array
    {
        $period = CarbonPeriod::create($simulation->start_date, $simulation->end_date);
        $days = $period->count();

        // Configuration par défaut
        $config = $simulation->scenario_config ?? [];
        $avgBetsPerDay = $config['avg_bets_per_day'] ?? 10;
        $avgBetAmount = $config['avg_bet_amount'] ?? 1000;
        $variance = $config['variance'] ?? 0.3; // 30% de variance

        $dailyData = [];
        $totalBets = 0;
        $totalWon = 0;
        $totalLost = 0;
        $betsWon = 0;
        $betsLost = 0;
        $gamesPlayed = 0;

        foreach ($period as $date) {
            $dayBets = $this->randomWithVariance($avgBetsPerDay, $variance);
            $dayGames = max(1, intval($dayBets / 2)); // En moyenne 2 paris par jeu

            // Calculer les gains/pertes selon le type de scénario
            $dayData = $this->calculateDayData(
                $simulation->scenario_type,
                $dayBets,
                $avgBetAmount,
                $variance
            );

            $totalBets += $dayBets;
            $betsWon += $dayData['bets_won'];
            $betsLost += $dayData['bets_lost'];
            $totalWon += $dayData['amount_won'];
            $totalLost += $dayData['amount_lost'];
            $gamesPlayed += $dayGames;

            $dailyData[] = [
                'date' => $date->format('Y-m-d'),
                'bets' => $dayBets,
                'games' => $dayGames,
                'bets_won' => $dayData['bets_won'],
                'bets_lost' => $dayData['bets_lost'],
                'amount_won' => round($dayData['amount_won'], 2),
                'amount_lost' => round($dayData['amount_lost'], 2),
                'net_profit' => round($dayData['amount_won'] - $dayData['amount_lost'], 2),
            ];
        }

        $totalAmount = $totalWon + $totalLost;
        $netProfit = $totalWon - $totalLost;

        return [
            'total_bets' => $totalBets,
            'bets_won' => $betsWon,
            'bets_lost' => $betsLost,
            'games_played' => $gamesPlayed,
            'total_amount' => round($totalAmount, 2),
            'total_won' => round($totalWon, 2),
            'total_lost' => round($totalLost, 2),
            'net_profit' => round($netProfit, 2),
            'daily_data' => $dailyData,
        ];
    }

    /**
     * Calculer les données pour une journée
     */
    protected function calculateDayData(string $scenarioType, int $bets, float $avgAmount, float $variance): array
    {
        $winRate = match($scenarioType) {
            'gain' => 0.65, // 65% de paris gagnés
            'perte' => 0.35, // 35% de paris gagnés
            'mixte' => 0.50, // 50% de paris gagnés
            default => 0.50,
        };

        $betsWon = 0;
        $amountWon = 0;
        $amountLost = 0;

        for ($i = 0; $i < $bets; $i++) {
            $betAmount = $this->randomWithVariance($avgAmount, $variance);
            $isWin = (mt_rand() / mt_getrandmax()) < $winRate;

            if ($isWin) {
                $betsWon++;
                // Les gains sont généralement 1.5x à 3x la mise
                $multiplier = 1.5 + (mt_rand() / mt_getrandmax()) * 1.5;
                $amountWon += $betAmount * $multiplier;
                $amountLost += $betAmount; // La mise initiale
            } else {
                $amountLost += $betAmount;
            }
        }

        return [
            'bets_won' => $betsWon,
            'bets_lost' => $bets - $betsWon,
            'amount_won' => $amountWon,
            'amount_lost' => $amountLost,
        ];
    }

    /**
     * Générer un nombre aléatoire avec variance
     */
    protected function randomWithVariance(float $base, float $variance): int|float
    {
        $min = $base * (1 - $variance);
        $max = $base * (1 + $variance);
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }

    /**
     * Obtenir les données de graphique formatées
     */
    public function getChartData(DemoSimulation $simulation): array
    {
        $dailyData = $simulation->daily_data ?? [];

        $labels = [];
        $profitData = [];
        $betsData = [];
        $winRateData = [];

        foreach ($dailyData as $day) {
            $labels[] = Carbon::parse($day['date'])->format('d/m');
            $profitData[] = $day['net_profit'];
            $betsData[] = $day['bets'];

            $totalDayBets = $day['bets_won'] + $day['bets_lost'];
            $winRateData[] = $totalDayBets > 0 ? round(($day['bets_won'] / $totalDayBets) * 100, 1) : 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Profit Net (FCFA)',
                    'data' => $profitData,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                ],
                [
                    'label' => 'Nombre de Paris',
                    'data' => $betsData,
                    'borderColor' => 'rgb(54, 162, 235)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                ],
                [
                    'label' => 'Taux de Réussite (%)',
                    'data' => $winRateData,
                    'borderColor' => 'rgb(255, 206, 86)',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                ],
            ],
        ];
    }

    /**
     * Activer une simulation (désactive automatiquement les autres)
     */
    public function activateSimulation(DemoSimulation $simulation): void
    {
        $simulation->update([
            'is_active' => true,
            'is_preview' => false,
        ]);
    }

    /**
     * Désactiver une simulation
     */
    public function deactivateSimulation(DemoSimulation $simulation): void
    {
        $simulation->update([
            'is_active' => false,
        ]);
    }
}
