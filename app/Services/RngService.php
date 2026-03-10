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

        // Pour les cartes à gratter, générer directement selon win/loss
        if ($game->type === GameType::SCRATCH_CARD) {
            $result = $isWinner
                ? $this->generateWinningScratchCard($hash)
                : $this->generateLosingScratchCard($hash);
        } elseif ($game->type === GameType::COIN_FLIP) {
            // Pour Pile ou Face : si isWinner, retourner le choix du joueur
            // Sinon, retourner l'opposé
            if ($isWinner) {
                $result = strtolower($bet->choice); // heads ou tails (choix du joueur)
            } else {
                // Retourner l'opposé du choix
                $result = strtolower($bet->choice) === 'heads' ? 'tails' : 'heads';
            }
        } elseif ($game->type === GameType::DICE) {
            // Pour le Dé : si isWinner, générer un résultat qui correspond au choix
            if ($isWinner) {
                $result = $this->generateWinningDiceResult($bet->choice, $hash);
            } else {
                $result = $this->generateLosingDiceResult($bet->choice, $hash);
            }
        } elseif ($game->type === GameType::TREASURE_BOX) {
            // Pour le Coffre au Trésor : si isWinner, retourner le choix du joueur
            // Sinon, retourner un autre coffre
            if ($isWinner) {
                $result = $bet->choice; // Le coffre choisi par le joueur
            } else {
                $result = $this->generateLosingTreasureBox($bet->choice, $hash, $game);
            }
        } elseif ($game->type === GameType::LUCKY_NUMBER) {
            // Pour le Nombre Chanceux : si isWinner, retourner le choix du joueur
            // Sinon, retourner un autre nombre
            if ($isWinner) {
                $result = $bet->choice; // Le nombre choisi par le joueur
            } else {
                $result = $this->generateLosingLuckyNumber($bet->choice, $hash, $game);
            }
        } elseif ($game->type === GameType::COLOR_ROULETTE) {
            // Pour la Roulette Couleurs : si isWinner, retourner le choix du joueur
            // Sinon, retourner une autre couleur
            if ($isWinner) {
                $result = $bet->choice; // La couleur choisie par le joueur
            } else {
                $result = $this->generateLosingColorRoulette($bet->choice, $hash, $game);
            }
        } elseif ($game->type === GameType::LUDO) {
            // Pour Course de Pions : si isWinner, retourner le choix du joueur
            // Sinon, retourner un autre pion
            if ($isWinner) {
                $result = $bet->choice; // Le pion choisi par le joueur
            } else {
                $result = $this->generateLosingLudo($bet->choice, $hash);
            }
        } elseif ($game->type === GameType::QUIZ) {
            // Pour Quiz : si isWinner, retourner le choix du joueur
            // Sinon, retourner une autre réponse
            if ($isWinner) {
                $result = $bet->choice; // La réponse choisie par le joueur
            } else {
                $result = $this->generateLosingQuiz($bet->choice, $hash);
            }
        } elseif ($game->type === GameType::ROULETTE || $game->type === GameType::JACKPOT) {
            // Pour la Roulette et Jackpot : la win_frequency est gérée par la distribution des segments
            // On tire un segment aléatoire, puis on vérifie si c'est un segment gagnant
            $result = $this->generateGameSpecificResult($game, $bet->choice, $hash);

            // Récupérer le multiplicateur du segment
            $multiplier = $this->getMultiplier($game, $result, $hash);

            // Le segment est gagnant si son multiplicateur > 0
            $isWinner = $multiplier > 0;
        } else {
            // Générer le résultat spécifique au type de jeu
            $result = $this->generateGameSpecificResult($game, $bet->choice, $hash);
            // Vérifier si le choix du joueur est gagnant
            $isWinner = $isWinner && $this->checkChoice($game, $bet->choice, $result);
        }

        // Calculer le multiplicateur et le payout
        // Pour la roulette, le multiplicateur est déjà calculé ci-dessus
        if ($game->type !== GameType::ROULETTE && $game->type !== GameType::JACKPOT) {
            $multiplier = $isWinner ? $this->getMultiplier($game, $result, $hash) : 0;
        }

        // Payout = (mise × multiplicateur) + mise de retour
        $payout = $isWinner ? ($bet->amount * $multiplier) + $bet->amount : 0;

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

    /**
     * Générer un résultat de dé GAGNANT selon le choix du joueur
     */
    protected function generateWinningDiceResult(string $choice, string $hash): string
    {
        if ($choice === 'odd') {
            // Retourner un nombre impair (1, 3, 5)
            $oddNumbers = [1, 3, 5];
            $index = hexdec(substr($hash, 8, 2)) % count($oddNumbers);
            return (string) $oddNumbers[$index];
        } elseif ($choice === 'even') {
            // Retourner un nombre pair (2, 4, 6)
            $evenNumbers = [2, 4, 6];
            $index = hexdec(substr($hash, 8, 2)) % count($evenNumbers);
            return (string) $evenNumbers[$index];
        } else {
            // Le joueur a choisi un numéro spécifique, retourner ce numéro
            return $choice;
        }
    }

    /**
     * Générer un résultat de dé PERDANT selon le choix du joueur
     */
    protected function generateLosingDiceResult(string $choice, string $hash): string
    {
        if ($choice === 'odd') {
            // Retourner un nombre pair (2, 4, 6)
            $evenNumbers = [2, 4, 6];
            $index = hexdec(substr($hash, 8, 2)) % count($evenNumbers);
            return (string) $evenNumbers[$index];
        } elseif ($choice === 'even') {
            // Retourner un nombre impair (1, 3, 5)
            $oddNumbers = [1, 3, 5];
            $index = hexdec(substr($hash, 8, 2)) % count($oddNumbers);
            return (string) $oddNumbers[$index];
        } else {
            // Le joueur a choisi un numéro spécifique, retourner un autre numéro
            $allNumbers = [1, 2, 3, 4, 5, 6];
            $otherNumbers = array_diff($allNumbers, [(int) $choice]);
            $otherNumbers = array_values($otherNumbers); // Réindexer
            $index = hexdec(substr($hash, 8, 2)) % count($otherNumbers);
            return (string) $otherNumbers[$index];
        }
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
        // Pour la roulette, on tire un segment aléatoire parmi tous les segments (gagnants + perdants)
        // La win_frequency est déjà gérée par la distribution des segments
        $prizes = $game->settings['prizes'] ?? [];
        $segments = count($prizes);

        if ($segments === 0) {
            $segments = $game->settings['segments'] ?? 8;
        }

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

    /**
     * Générer un résultat de coffre PERDANT (différent du choix du joueur)
     */
    protected function generateLosingTreasureBox(string $choice, string $hash, Game $game): string
    {
        $boxes = $game->settings['boxes_count'] ?? 3;
        $playerChoice = (int) $choice;

        // Générer tous les coffres sauf celui choisi par le joueur
        $otherBoxes = [];
        for ($i = 1; $i <= $boxes; $i++) {
            if ($i !== $playerChoice) {
                $otherBoxes[] = $i;
            }
        }

        // Choisir un coffre aléatoire parmi les autres
        $index = hexdec(substr($hash, 8, 2)) % count($otherBoxes);
        return (string) $otherBoxes[$index];
    }

    /**
     * Générer un nombre PERDANT (différent du choix du joueur)
     */
    protected function generateLosingLuckyNumber(string $choice, string $hash, Game $game): string
    {
        $min = $game->settings['range_min'] ?? 1;
        $max = $game->settings['range_max'] ?? 10;
        $playerChoice = (int) $choice;

        // Générer tous les nombres sauf celui choisi par le joueur
        $otherNumbers = [];
        for ($i = $min; $i <= $max; $i++) {
            if ($i !== $playerChoice) {
                $otherNumbers[] = $i;
            }
        }

        // Choisir un nombre aléatoire parmi les autres
        $index = hexdec(substr($hash, 10, 2)) % count($otherNumbers);
        return (string) $otherNumbers[$index];
    }

    /**
     * Générer une couleur PERDANTE (différente du choix du joueur)
     */
    protected function generateLosingColorRoulette(string $choice, string $hash, Game $game): string
    {
        $colors = $game->settings['colors'] ?? ['red', 'blue', 'green', 'yellow'];
        $playerChoice = strtolower($choice);

        // Générer toutes les couleurs sauf celle choisie par le joueur
        $otherColors = [];
        foreach ($colors as $color) {
            if (strtolower($color) !== $playerChoice) {
                $otherColors[] = $color;
            }
        }

        // Choisir une couleur aléatoire parmi les autres
        $index = hexdec(substr($hash, 12, 2)) % count($otherColors);
        return $otherColors[$index];
    }

    /**
     * Générer un pion PERDANT (différent du choix du joueur)
     */
    protected function generateLosingLudo(string $choice, string $hash): string
    {
        $players = ['red', 'blue', 'green', 'yellow'];
        $playerChoice = strtolower($choice);

        // Générer tous les pions sauf celui choisi par le joueur
        $otherPlayers = [];
        foreach ($players as $player) {
            if (strtolower($player) !== $playerChoice) {
                $otherPlayers[] = $player;
            }
        }

        // Choisir un pion aléatoire parmi les autres
        $index = hexdec(substr($hash, 14, 2)) % count($otherPlayers);
        return $otherPlayers[$index];
    }

    /**
     * Générer une réponse PERDANTE (différente du choix du joueur)
     */
    protected function generateLosingQuiz(string $choice, string $hash): string
    {
        $options = ['A', 'B', 'C', 'D'];
        $playerChoice = strtoupper($choice);

        // Générer toutes les réponses sauf celle choisie par le joueur
        $otherOptions = [];
        foreach ($options as $option) {
            if ($option !== $playerChoice) {
                $otherOptions[] = $option;
            }
        }

        // Choisir une réponse aléatoire parmi les autres
        $index = hexdec(substr($hash, 16, 2)) % count($otherOptions);
        return $otherOptions[$index];
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
        // Génère un pattern de carte à gratter AUTHENTIQUE
        $symbols = ['star', 'diamond', 'heart', 'club', 'spade'];
        $result = [];

        // Le résultat sera déterminé par checkScratchCardWin qui doit retourner true/false
        // On génère 9 symboles aléatoires comme base
        for ($i = 0; $i < 9; $i++) {
            $index = hexdec(substr($hash, $i * 2, 2)) % count($symbols);
            $result[] = $symbols[$index];
        }

        return implode(',', $result);
    }

    /**
     * Génère une carte GAGNANTE avec garantie de 3+ symboles identiques
     */
    protected function generateWinningScratchCard(string $hash): string
    {
        $symbols = ['star', 'diamond', 'heart', 'club', 'spade'];

        // Choisir le symbole gagnant
        $winningSymbolIndex = hexdec(substr($hash, 0, 2)) % count($symbols);
        $winningSymbol = $symbols[$winningSymbolIndex];

        // Déterminer combien de symboles identiques (3 à 9)
        $matchCount = 3 + (hexdec(substr($hash, 2, 2)) % 7); // 3 à 9

        // Créer la grille avec le symbole gagnant
        $grid = [];
        for ($i = 0; $i < $matchCount; $i++) {
            $grid[] = $winningSymbol;
        }

        // Remplir le reste avec des symboles différents
        $otherSymbols = array_diff($symbols, [$winningSymbol]);
        for ($i = $matchCount; $i < 9; $i++) {
            $randomIndex = hexdec(substr($hash, $i * 2, 2)) % count($otherSymbols);
            $grid[] = array_values($otherSymbols)[$randomIndex];
        }

        // Mélanger la grille pour que ce soit aléatoire visuellement
        $seed = hexdec(substr($hash, 10, 8));
        mt_srand($seed);
        shuffle($grid);

        return implode(',', $grid);
    }

    /**
     * Génère une carte PERDANTE avec maximum 2 symboles identiques
     */
    protected function generateLosingScratchCard(string $hash): string
    {
        $symbols = ['star', 'diamond', 'heart', 'club', 'spade'];
        $grid = [];

        // Stratégie: 2 de chaque symbole maximum pour éviter les gains
        // Distribution: 2-2-2-2-1 (9 cases)
        $distribution = [2, 2, 2, 2, 1];

        $symbolIndex = 0;
        foreach ($distribution as $count) {
            $symbol = $symbols[$symbolIndex % count($symbols)];
            for ($i = 0; $i < $count; $i++) {
                $grid[] = $symbol;
            }
            $symbolIndex++;
        }

        // Mélanger pour rendre aléatoire
        $seed = hexdec(substr($hash, 10, 8));
        mt_srand($seed);
        shuffle($grid);

        return implode(',', $grid);
    }

    protected function jackpotResult(string $hash, Game $game): string
    {
        $segments = $game->settings['segments'] ?? 6;
        $segment = (hexdec(substr($hash, 8, 4)) % $segments) + 1;
        return (string) $segment;
    }

    protected function penaltyResult(string $hash): string
    {
        // Retourner un numéro de position (1-5) au lieu d'un nom
        // Pour correspondre avec le choix du joueur qui envoie "1", "2", "3", "4", ou "5"
        $positions = 5; // 5 positions disponibles
        $position = (hexdec(substr($hash, 8, 2)) % $positions) + 1;
        return (string) $position;
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
            // Pour la roulette, si choice = 'auto', c'est toujours vrai (win_frequency déjà vérifié)
            GameType::ROULETTE, GameType::JACKPOT => $choice === 'auto' ? true : $choice === $result,
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

        // Pour la roulette, utiliser le multiplicateur du segment
        if ($type === GameType::ROULETTE) {
            $prizes = $game->settings['prizes'] ?? [];
            $segment = (int) $result;

            if (isset($prizes[$segment]['multiplier'])) {
                $multiplier = (float) $prizes[$segment]['multiplier'];

                // Si le multiplicateur est 0, c'est un segment perdant
                if ($multiplier === 0.0) {
                    return 0;
                }

                return $multiplier;
            }
        }

        // Pour les autres jeux, sélectionner aléatoirement un multiplicateur
        $index = hexdec(substr($hash, 16, 2)) % count($multipliers);
        return (float) $multipliers[$index];
    }
}
