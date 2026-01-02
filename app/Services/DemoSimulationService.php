<?php

namespace App\Services;

use App\Models\DemoConfiguration;
use App\Models\DemoSimulatedData;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemoSimulationService
{
    /**
     * Generate simulated data for a demo configuration
     */
    public function generateData(DemoConfiguration $config, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? $config->start_date;
        $endDate = $endDate ?? ($config->end_date ?? now());

        $generatedData = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dailyData = $this->generateDailyData($config, $currentDate);
            $generatedData[] = $dailyData;

            $currentDate->addDay();
        }

        return $generatedData;
    }

    /**
     * Generate daily simulated data
     */
    protected function generateDailyData(DemoConfiguration $config, Carbon $date): DemoSimulatedData
    {
        $games = $config->games();
        if ($games->isEmpty()) {
            $games = Game::where('is_active', true)->limit(5)->get();
        }

        $dailyBetCount = $config->daily_bet_count;
        $totalBetAmount = 0;
        $totalWinAmount = 0;
        $totalLossAmount = 0;
        $winCount = 0;
        $lossCount = 0;
        $hourlyData = [];
        $gameBreakdown = [];

        // Generate hourly data
        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyBets = $this->getHourlyBetCount($hour, $dailyBetCount);
            $hourlyData[$hour] = [
                'hour' => $hour,
                'bet_count' => $hourlyBets,
                'total_bet' => 0,
                'total_win' => 0,
                'total_loss' => 0,
            ];
        }

        // Distribute bets across hours and games
        for ($i = 0; $i < $dailyBetCount; $i++) {
            $game = $games->random();
            $hour = $this->getRandomHourWeighted();

            $betAmount = $this->getRandomBetAmount($config);
            $isWin = $this->shouldWin($config->win_rate);

            $totalBetAmount += $betAmount;

            if ($isWin) {
                $multiplier = $this->getRandomMultiplier($config);
                $winAmount = $betAmount * $multiplier;
                $totalWinAmount += $winAmount;
                $winCount++;

                $hourlyData[$hour]['total_win'] += $winAmount;
            } else {
                $totalLossAmount += $betAmount;
                $lossCount++;

                $hourlyData[$hour]['total_loss'] += $betAmount;
            }

            $hourlyData[$hour]['total_bet'] += $betAmount;

            // Track game breakdown
            if (!isset($gameBreakdown[$game->id])) {
                $gameBreakdown[$game->id] = [
                    'game_id' => $game->id,
                    'game_name' => $game->name,
                    'bet_count' => 0,
                    'total_bet' => 0,
                    'total_win' => 0,
                    'total_loss' => 0,
                ];
            }

            $gameBreakdown[$game->id]['bet_count']++;
            $gameBreakdown[$game->id]['total_bet'] += $betAmount;

            if ($isWin) {
                $gameBreakdown[$game->id]['total_win'] += $winAmount;
            } else {
                $gameBreakdown[$game->id]['total_loss'] += $betAmount;
            }
        }

        $netAmount = $totalWinAmount - $totalLossAmount;
        $winRateActual = $dailyBetCount > 0 ? ($winCount / $dailyBetCount) * 100 : 0;

        // Create or update simulated data
        return DemoSimulatedData::updateOrCreate(
            [
                'demo_configuration_id' => $config->id,
                'user_id' => $config->user_id,
                'date' => $date,
                'period_type' => 'daily',
            ],
            [
                'total_bet_amount' => $totalBetAmount,
                'total_win_amount' => $totalWinAmount,
                'total_loss_amount' => $totalLossAmount,
                'net_amount' => $netAmount,
                'bet_count' => $dailyBetCount,
                'win_count' => $winCount,
                'loss_count' => $lossCount,
                'win_rate_actual' => round($winRateActual, 2),
                'hourly_data' => array_values($hourlyData),
                'game_breakdown' => array_values($gameBreakdown),
            ]
        );
    }

    /**
     * Get hourly bet count distribution (more activity during peak hours)
     */
    protected function getHourlyBetCount(int $hour, int $dailyTotal): int
    {
        // Peak hours: 12-14h and 18-23h
        $weights = [
            0 => 0.5, 1 => 0.3, 2 => 0.2, 3 => 0.1, 4 => 0.1, 5 => 0.2,
            6 => 0.5, 7 => 0.8, 8 => 1.0, 9 => 1.2, 10 => 1.5, 11 => 1.8,
            12 => 2.0, 13 => 2.0, 14 => 1.8, 15 => 1.5, 16 => 1.8, 17 => 2.0,
            18 => 2.5, 19 => 3.0, 20 => 3.5, 21 => 3.0, 22 => 2.5, 23 => 1.5,
        ];

        $totalWeight = array_sum($weights);
        return (int) round(($weights[$hour] / $totalWeight) * $dailyTotal);
    }

    /**
     * Get random hour with weighted distribution
     */
    protected function getRandomHourWeighted(): int
    {
        $weights = [
            0 => 0.5, 1 => 0.3, 2 => 0.2, 3 => 0.1, 4 => 0.1, 5 => 0.2,
            6 => 0.5, 7 => 0.8, 8 => 1.0, 9 => 1.2, 10 => 1.5, 11 => 1.8,
            12 => 2.0, 13 => 2.0, 14 => 1.8, 15 => 1.5, 16 => 1.8, 17 => 2.0,
            18 => 2.5, 19 => 3.0, 20 => 3.5, 21 => 3.0, 22 => 2.5, 23 => 1.5,
        ];

        $totalWeight = array_sum($weights);
        $random = mt_rand(0, (int) ($totalWeight * 100)) / 100;

        $sum = 0;
        foreach ($weights as $hour => $weight) {
            $sum += $weight;
            if ($random <= $sum) {
                return $hour;
            }
        }

        return 12; // Default to noon
    }

    /**
     * Get random bet amount
     */
    protected function getRandomBetAmount(DemoConfiguration $config): float
    {
        $min = $config->min_bet;
        $max = $config->max_bet;

        // Use log distribution to favor smaller bets
        $logMin = log($min);
        $logMax = log($max);
        $random = mt_rand() / mt_getrandmax();

        return round(exp($logMin + ($random * ($logMax - $logMin))), 2);
    }

    /**
     * Determine if bet should win based on win rate
     */
    protected function shouldWin(float $winRate): bool
    {
        return (mt_rand(1, 10000) / 100) <= $winRate;
    }

    /**
     * Get random win multiplier
     */
    protected function getRandomMultiplier(DemoConfiguration $config): float
    {
        $min = $config->min_win_multiplier;
        $max = $config->max_win_multiplier;

        // Use exponential distribution to favor smaller multipliers
        $random = mt_rand() / mt_getrandmax();
        $exponential = pow($random, 2); // Square to favor lower values

        return round($min + ($exponential * ($max - $min)), 2);
    }

    /**
     * Generate weekly aggregated data from daily data
     */
    public function generateWeeklyData(DemoConfiguration $config, Carbon $weekStart): ?DemoSimulatedData
    {
        $weekEnd = $weekStart->copy()->addDays(6);

        $dailyData = DemoSimulatedData::forConfiguration($config->id)
            ->dateRange($weekStart, $weekEnd)
            ->periodType('daily')
            ->get();

        if ($dailyData->isEmpty()) {
            return null;
        }

        $totals = $this->aggregateData($dailyData);

        return DemoSimulatedData::updateOrCreate(
            [
                'demo_configuration_id' => $config->id,
                'user_id' => $config->user_id,
                'date' => $weekStart,
                'period_type' => 'weekly',
            ],
            $totals
        );
    }

    /**
     * Generate monthly aggregated data from daily data
     */
    public function generateMonthlyData(DemoConfiguration $config, Carbon $monthStart): ?DemoSimulatedData
    {
        $monthEnd = $monthStart->copy()->endOfMonth();

        $dailyData = DemoSimulatedData::forConfiguration($config->id)
            ->dateRange($monthStart, $monthEnd)
            ->periodType('daily')
            ->get();

        if ($dailyData->isEmpty()) {
            return null;
        }

        $totals = $this->aggregateData($dailyData);

        return DemoSimulatedData::updateOrCreate(
            [
                'demo_configuration_id' => $config->id,
                'user_id' => $config->user_id,
                'date' => $monthStart,
                'period_type' => 'monthly',
            ],
            $totals
        );
    }

    /**
     * Aggregate daily data into totals
     */
    protected function aggregateData($dataCollection): array
    {
        $totalBetAmount = $dataCollection->sum('total_bet_amount');
        $totalWinAmount = $dataCollection->sum('total_win_amount');
        $totalLossAmount = $dataCollection->sum('total_loss_amount');
        $betCount = $dataCollection->sum('bet_count');
        $winCount = $dataCollection->sum('win_count');
        $lossCount = $dataCollection->sum('loss_count');

        $winRateActual = $betCount > 0 ? ($winCount / $betCount) * 100 : 0;

        return [
            'total_bet_amount' => $totalBetAmount,
            'total_win_amount' => $totalWinAmount,
            'total_loss_amount' => $totalLossAmount,
            'net_amount' => $totalWinAmount - $totalLossAmount,
            'bet_count' => $betCount,
            'win_count' => $winCount,
            'loss_count' => $lossCount,
            'win_rate_actual' => round($winRateActual, 2),
        ];
    }

    /**
     * Delete all simulated data for a configuration
     */
    public function clearData(DemoConfiguration $config): int
    {
        return DemoSimulatedData::forConfiguration($config->id)->delete();
    }

    /**
     * Get statistics for a configuration
     */
    public function getStatistics(DemoConfiguration $config, string $periodType = 'daily'): array
    {
        $data = DemoSimulatedData::forConfiguration($config->id)
            ->periodType($periodType)
            ->orderBy('date')
            ->get();

        return [
            'total_bet_amount' => $data->sum('total_bet_amount'),
            'total_win_amount' => $data->sum('total_win_amount'),
            'total_loss_amount' => $data->sum('total_loss_amount'),
            'net_amount' => $data->sum('net_amount'),
            'total_bets' => $data->sum('bet_count'),
            'total_wins' => $data->sum('win_count'),
            'total_losses' => $data->sum('loss_count'),
            'average_win_rate' => $data->avg('win_rate_actual'),
            'data_points' => $data->count(),
            'chart_data' => $data->map(function ($item) {
                return [
                    'date' => $item->date->format('Y-m-d'),
                    'bet_amount' => (float) $item->total_bet_amount,
                    'win_amount' => (float) $item->total_win_amount,
                    'loss_amount' => (float) $item->total_loss_amount,
                    'net_amount' => (float) $item->net_amount,
                    'bet_count' => $item->bet_count,
                    'win_rate' => (float) $item->win_rate_actual,
                ];
            })->values()->all(),
        ];
    }
}
