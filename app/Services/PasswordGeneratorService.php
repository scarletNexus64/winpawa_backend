<?php

namespace App\Services;

class PasswordGeneratorService
{
    /**
     * Génère un mot de passe sécurisé et mémorable
     * Format: Mot-Nombre-Symbole (ex: Lucky2024!)
     */
    public static function generate(): string
    {
        $words = [
            'Lucky', 'Happy', 'Winner', 'Gold', 'Star', 'Super', 'Mega',
            'Ultra', 'Power', 'Magic', 'Royal', 'Epic', 'Beast', 'King',
            'Queen', 'Tiger', 'Lion', 'Eagle', 'Wolf', 'Dragon', 'Phoenix',
            'Legend', 'Hero', 'Brave', 'Swift', 'Bold', 'Elite', 'Prime',
        ];

        $symbols = ['!', '@', '#', '$', '*', '+'];

        // Choisir un mot aléatoire
        $word = $words[array_rand($words)];

        // Nombre aléatoire entre 1000 et 9999
        $number = rand(1000, 9999);

        // Symbole aléatoire
        $symbol = $symbols[array_rand($symbols)];

        return $word . $number . $symbol;
    }

    /**
     * Génère un mot de passe plus complexe
     */
    public static function generateComplex(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $all = $uppercase . $lowercase . $numbers . $symbols;

        $password = '';

        // Assurer au moins un caractère de chaque type
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $symbols[rand(0, strlen($symbols) - 1)];

        // Compléter avec des caractères aléatoires
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }

        // Mélanger la chaîne
        return str_shuffle($password);
    }

    /**
     * Génère un PIN à 6 chiffres
     */
    public static function generatePin(): string
    {
        return str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Valide la force d'un mot de passe
     */
    public static function validateStrength(string $password): array
    {
        $strength = 0;
        $feedback = [];

        // Longueur
        if (strlen($password) >= 8) {
            $strength += 1;
        } else {
            $feedback[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        // Majuscules
        if (preg_match('/[A-Z]/', $password)) {
            $strength += 1;
        } else {
            $feedback[] = 'Ajoutez au moins une majuscule';
        }

        // Minuscules
        if (preg_match('/[a-z]/', $password)) {
            $strength += 1;
        } else {
            $feedback[] = 'Ajoutez au moins une minuscule';
        }

        // Chiffres
        if (preg_match('/[0-9]/', $password)) {
            $strength += 1;
        } else {
            $feedback[] = 'Ajoutez au moins un chiffre';
        }

        // Caractères spéciaux
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $strength += 1;
        } else {
            $feedback[] = 'Ajoutez au moins un caractère spécial';
        }

        return [
            'score' => $strength,
            'strength' => $strength >= 4 ? 'forte' : ($strength >= 3 ? 'moyenne' : 'faible'),
            'feedback' => $feedback,
        ];
    }
}
