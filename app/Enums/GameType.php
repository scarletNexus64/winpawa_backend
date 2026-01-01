<?php

namespace App\Enums;

enum GameType: string
{
    case ROULETTE = 'roulette';
    case SCRATCH_CARD = 'scratch_card';
    case COIN_FLIP = 'coin_flip';
    case DICE = 'dice';
    case ROCK_PAPER_SCISSORS = 'rock_paper_scissors';
    case TREASURE_BOX = 'treasure_box';
    case LUCKY_NUMBER = 'lucky_number';
    case JACKPOT = 'jackpot';
    case PENALTY = 'penalty';
    case LUDO = 'ludo';
    case QUIZ = 'quiz';
    case COLOR_ROULETTE = 'color_roulette';

    public function label(): string
    {
        return match ($this) {
            self::ROULETTE => 'Roulette / Apple of Fortune',
            self::SCRATCH_CARD => 'Cartes à gratter',
            self::COIN_FLIP => 'Pile ou Face',
            self::DICE => 'Lancer de dés',
            self::ROCK_PAPER_SCISSORS => 'Pierre-Papier-Ciseaux',
            self::TREASURE_BOX => 'Choix de coffre',
            self::LUCKY_NUMBER => 'Nombre aléatoire',
            self::JACKPOT => 'Jackpot simplifié',
            self::PENALTY => 'Tir au but',
            self::LUDO => 'Course de pions',
            self::QUIZ => 'Mini quiz chance',
            self::COLOR_ROULETTE => 'Roulette de couleurs',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ROULETTE => '🎰',
            self::SCRATCH_CARD => '🎫',
            self::COIN_FLIP => '🪙',
            self::DICE => '🎲',
            self::ROCK_PAPER_SCISSORS => '✊',
            self::TREASURE_BOX => '📦',
            self::LUCKY_NUMBER => '🔢',
            self::JACKPOT => '💰',
            self::PENALTY => '⚽',
            self::LUDO => '🎯',
            self::QUIZ => '❓',
            self::COLOR_ROULETTE => '🎨',
        };
    }

    public function defaultRtp(): float
    {
        return match ($this) {
            self::ROULETTE => 80.0,
            self::SCRATCH_CARD => 75.0,
            self::COIN_FLIP => 77.5,
            self::DICE => 77.5,
            self::ROCK_PAPER_SCISSORS => 77.5,
            self::TREASURE_BOX => 77.5,
            self::LUCKY_NUMBER => 77.5,
            self::JACKPOT => 75.0,
            self::PENALTY => 77.5,
            self::LUDO => 77.5,
            self::QUIZ => 77.5,
            self::COLOR_ROULETTE => 77.5,
        };
    }

    public function defaultWinFrequency(): float
    {
        return match ($this) {
            self::ROULETTE => 40.0,
            self::SCRATCH_CARD => 35.0,
            self::COIN_FLIP => 50.0,
            self::DICE => 40.0,
            self::ROCK_PAPER_SCISSORS => 33.0,
            self::TREASURE_BOX => 30.0,
            self::LUCKY_NUMBER => 20.0,
            self::JACKPOT => 25.0,
            self::PENALTY => 40.0,
            self::LUDO => 35.0,
            self::QUIZ => 50.0,
            self::COLOR_ROULETTE => 33.0,
        };
    }

    public function defaultMultipliers(): array
    {
        return match ($this) {
            self::ROULETTE => [2, 5, 10],
            self::SCRATCH_CARD => [2, 3, 5, 10],
            self::COIN_FLIP => [2],
            self::DICE => [2, 3],
            self::ROCK_PAPER_SCISSORS => [2],
            self::TREASURE_BOX => [2, 3, 5],
            self::LUCKY_NUMBER => [2, 3],
            self::JACKPOT => [2, 3, 5, 10],
            self::PENALTY => [2, 3, 5],
            self::LUDO => [2, 3, 5],
            self::QUIZ => [2],
            self::COLOR_ROULETTE => [2, 3],
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::ROULETTE, self::SCRATCH_CARD, self::JACKPOT => 'Jeux de Chance',
            self::COIN_FLIP, self::DICE, self::LUCKY_NUMBER, self::COLOR_ROULETTE => 'Jeux de Prédiction',
            self::PENALTY, self::ROCK_PAPER_SCISSORS => 'Jeux d\'Action',
            self::TREASURE_BOX, self::LUDO, self::QUIZ => 'Jeux de Stratégie',
        };
    }

    public static function categoryIcon(string $category): string
    {
        return match ($category) {
            'Jeux de Chance' => '🎰',
            'Jeux de Prédiction' => '🔮',
            'Jeux d\'Action' => '⚡',
            'Jeux de Stratégie' => '🧠',
            default => '🎮',
        };
    }

    public function categoryColor(): string
    {
        return match ($this) {
            self::ROULETTE, self::SCRATCH_CARD, self::JACKPOT => 'success',
            self::COIN_FLIP, self::DICE, self::LUCKY_NUMBER, self::COLOR_ROULETTE => 'info',
            self::PENALTY, self::ROCK_PAPER_SCISSORS => 'danger',
            self::TREASURE_BOX, self::LUDO, self::QUIZ => 'warning',
        };
    }

    public static function getCategoriesOptions(): array
    {
        return [
            'Jeux de Chance' => 'Jeux de Chance',
            'Jeux de Prédiction' => 'Jeux de Prédiction',
            'Jeux d\'Action' => 'Jeux d\'Action',
            'Jeux de Stratégie' => 'Jeux de Stratégie',
        ];
    }
}
