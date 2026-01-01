<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Bet;
use App\Enums\GameType;

class RngService
{
    public function generateResult(Game $game, Bet $bet): array
    {
        // Utiliser le seed du bet pour la reproductibilité
        $hash = hash('sha256', $bet->rng_seed . $bet->id . microtime());
        $randomValue = hexdec(substr($hash, 0, 8)) / 0xFFFFFFFF * 100;

        // Déterminer si c'est un gain basé sur la fréquence
        $isWinner = $randomValue <= $game->win_frequency;

        // Générer le résultat spécifique au type de jeu
        $result = $this->generateGameSpecificResult($game, $bet->choice, $hash);

        // Vérifier si le choix du joueur est gagnant
        $isWinner = $isWinner && $this->checkChoice($game, $bet->choice, $result);

        // Calculer le multiplicateur et le payout
        $multiplier = $isWinner ? $this->getMultiplier($game, $result, $hash) : 0;
        $payout = $isWinner ? $bet->amount * $multiplier : 0;

        return [
            'result' => $result,
            'is_winner' => $isWinner,
            'multiplier' => $multiplier,
            'payout' => $payout,
        ];
    }

    protected function generateGameSpecificResult(Game $game, string $choice, string $hash): string
    {
        $type = $game->type;

        return match ($type) {
            GameType::COIN_FLIP => $this->coinFlipResult($hash),
            GameType::DICE => $this->diceResult($hash),
            GameType::ROCK_PAPER_SCISSORS => $this->rpsResult($hash),
            GameType::ROULETTE => $this->rouletteResult($hash, $game),
            GameType::COLOR_ROULETTE => $this->colorRouletteResult($hash, $game),
            GameType::TREASURE_BOX => $this->treasureBoxResult($hash, $game),
            GameType::LUCKY_NUMBER => $this->luckyNumberResult($hash, $game),
            GameType::SCRATCH_CARD => $this->scratchCardResult($hash),
            GameType::JACKPOT => $this->jackpotResult($hash, $game),
            GameType::PENALTY => $this->penaltyResult($hash),
            GameType::LUDO => $this->ludoResult($hash),
            GameType::QUIZ => $this->quizResult($hash),
            default => 'unknown',
        };
    }

    protected function coinFlipResult(string $hash): string
    {
        $value = hexdec(substr($hash, 8, 2)) % 2;
        return $value === 0 ? 'heads' : 'tails';
    }

    protected function diceResult(string $hash): string
    {
        $value = (hexdec(substr($hash, 8, 2)) % 6) + 1;
        return (string) $value;
    }

    protected function rpsResult(string $hash): string
    {
        $options = ['rock', 'paper', 'scissors'];
        $index = hexdec(substr($hash, 8, 2)) % 3;
        return $options[$index];
    }

    protected function rouletteResult(string $hash, Game $game): string
    {
        $segments = $game->settings['segments'] ?? 8;
        $segment = (hexdec(substr($hash, 8, 4)) % $segments) + 1;
        return (string) $segment;
    }

    protected function colorRouletteResult(string $hash, Game $game): string
    {
        $colors = $game->settings['colors'] ?? ['red', 'blue', 'green', 'yellow'];
        $index = hexdec(substr($hash, 8, 2)) % count($colors);
        return $colors[$index];
    }

    protected function treasureBoxResult(string $hash, Game $game): string
    {
        $boxes = $game->settings['boxes_count'] ?? 3;
        $winningBox = (hexdec(substr($hash, 8, 2)) % $boxes) + 1;
        return (string) $winningBox;
    }

    protected function luckyNumberResult(string $hash, Game $game): string
    {
        $min = $game->settings['range_min'] ?? 1;
        $max = $game->settings['range_max'] ?? 10;
        $range = $max - $min + 1;
        $number = (hexdec(substr($hash, 8, 4)) % $range) + $min;
        return (string) $number;
    }

    protected function scratchCardResult(string $hash): string
    {
        // Génère un pattern de carte à gratter
        $symbols = ['star', 'diamond', 'heart', 'club', 'spade'];
        $result = [];
        for ($i = 0; $i < 9; $i++) {
            $index = hexdec(substr($hash, $i * 2, 2)) % count($symbols);
            $result[] = $symbols[$index];
        }
        return implode(',', $result);
    }

    protected function jackpotResult(string $hash, Game $game): string
    {
        $segments = $game->settings['segments'] ?? 6;
        $segment = (hexdec(substr($hash, 8, 4)) % $segments) + 1;
        return (string) $segment;
    }

    protected function penaltyResult(string $hash): string
    {
        $positions = ['left', 'center', 'right', 'top_left', 'top_right'];
        $index = hexdec(substr($hash, 8, 2)) % count($positions);
        return $positions[$index];
    }

    protected function ludoResult(string $hash): string
    {
        $players = ['red', 'blue', 'green', 'yellow'];
        $index = hexdec(substr($hash, 8, 2)) % count($players);
        return $players[$index];
    }

    protected function quizResult(string $hash): string
    {
        $options = ['A', 'B', 'C', 'D'];
        $index = hexdec(substr($hash, 8, 2)) % count($options);
        return $options[$index];
    }

    protected function checkChoice(Game $game, string $choice, string $result): bool
    {
        $type = $game->type;

        return match ($type) {
            GameType::COIN_FLIP, 
            GameType::COLOR_ROULETTE,
            GameType::TREASURE_BOX,
            GameType::LUCKY_NUMBER,
            GameType::PENALTY,
            GameType::LUDO,
            GameType::QUIZ => strtolower($choice) === strtolower($result),

            GameType::DICE => $this->checkDiceChoice($choice, $result),
            GameType::ROCK_PAPER_SCISSORS => $this->checkRpsChoice($choice, $result),
            GameType::ROULETTE, GameType::JACKPOT => $choice === $result,
            GameType::SCRATCH_CARD => $this->checkScratchCardWin($result),
            default => false,
        };
    }

    protected function checkDiceChoice(string $choice, string $result): bool
    {
        $diceValue = (int) $result;

        if ($choice === 'odd') {
            return $diceValue % 2 === 1;
        }
        if ($choice === 'even') {
            return $diceValue % 2 === 0;
        }

        return $choice === $result;
    }

    protected function checkRpsChoice(string $choice, string $result): bool
    {
        $wins = [
            'rock' => 'scissors',
            'paper' => 'rock',
            'scissors' => 'paper',
        ];

        return isset($wins[$choice]) && $wins[$choice] === $result;
    }

    protected function checkScratchCardWin(string $result): bool
    {
        $symbols = explode(',', $result);
        $counts = array_count_values($symbols);
        $maxCount = max($counts);

        return $maxCount >= 3;
    }

    protected function getMultiplier(Game $game, string $result, string $hash): float
    {
        $multipliers = $game->multipliers;

        if (empty($multipliers)) {
            return 2.0;
        }

        // Pour certains jeux, le multiplicateur dépend du résultat
        $type = $game->type;

        if ($type === GameType::SCRATCH_CARD) {
            $symbols = explode(',', $result);
            $maxCount = max(array_count_values($symbols));
            
            return match ($maxCount) {
                3 => $multipliers[0] ?? 2,
                4 => $multipliers[1] ?? 3,
                5 => $multipliers[2] ?? 5,
                default => $multipliers[count($multipliers) - 1] ?? 10,
            };
        }

        // Pour les autres jeux, sélectionner aléatoirement un multiplicateur
        $index = hexdec(substr($hash, 16, 2)) % count($multipliers);
        return (float) $multipliers[$index];
    }
}
