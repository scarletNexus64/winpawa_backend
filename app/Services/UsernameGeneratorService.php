<?php

namespace App\Services;

use App\Models\User;

class UsernameGeneratorService
{
    /**
     * Liste d'adjectifs sympas pour les noms d'utilisateur
     */
    private static array $adjectives = [
        'Happy', 'Lucky', 'Swift', 'Bold', 'Brave', 'Wise', 'Quick',
        'Smart', 'Cool', 'Epic', 'Wild', 'Gold', 'Silver', 'Diamond',
        'Royal', 'Mega', 'Super', 'Ultra', 'Prime', 'Elite', 'Pro',
        'Ninja', 'Dragon', 'Tiger', 'Lion', 'Eagle', 'Wolf', 'Fox',
        'Star', 'Moon', 'Sun', 'Fire', 'Ice', 'Storm', 'Thunder',
    ];

    /**
     * Liste de noms d'animaux/objets pour les noms d'utilisateur
     */
    private static array $nouns = [
        'Panda', 'Tiger', 'Lion', 'Eagle', 'Wolf', 'Fox', 'Bear',
        'Hawk', 'Falcon', 'Phoenix', 'Dragon', 'Shark', 'Panther',
        'Cheetah', 'Leopard', 'Jaguar', 'Cobra', 'Viper', 'Python',
        'Raven', 'Owl', 'Sparrow', 'Robin', 'Warrior', 'Knight',
        'Champion', 'Victor', 'Legend', 'Hero', 'Master', 'Ace',
        'King', 'Queen', 'Prince', 'Duke', 'Baron', 'Lord',
    ];

    /**
     * Génère un nom d'utilisateur unique
     * Format: Adjectif + Nom + Nombre (ex: LuckyPanda35)
     */
    public static function generate(): string
    {
        $maxAttempts = 50;
        $attempts = 0;

        do {
            // Choisir un adjectif et un nom aléatoires
            $adjective = static::$adjectives[array_rand(static::$adjectives)];
            $noun = static::$nouns[array_rand(static::$nouns)];

            // Générer un nombre aléatoire entre 10 et 999
            $number = rand(10, 999);

            // Créer le nom d'utilisateur
            $username = $adjective . $noun . $number;

            $attempts++;

            // Vérifier si le nom d'utilisateur existe déjà
            if (!User::where('name', $username)->exists()) {
                return $username;
            }

        } while ($attempts < $maxAttempts);

        // Si on n'arrive pas à générer un nom unique après plusieurs tentatives,
        // ajouter un hash unique
        return $adjective . $noun . substr(md5(uniqid()), 0, 6);
    }

    /**
     * Génère un nom d'utilisateur unique avec un préfixe personnalisé
     */
    public static function generateWithPrefix(string $prefix): string
    {
        $maxAttempts = 50;
        $attempts = 0;

        do {
            // Générer un nombre aléatoire
            $number = rand(100, 9999);
            $username = $prefix . $number;

            $attempts++;

            if (!User::where('name', $username)->exists()) {
                return $username;
            }

        } while ($attempts < $maxAttempts);

        return $prefix . substr(md5(uniqid()), 0, 6);
    }

    /**
     * Valide un nom d'utilisateur
     */
    public static function validate(string $username): bool
    {
        // Longueur entre 3 et 30 caractères
        if (strlen($username) < 3 || strlen($username) > 30) {
            return false;
        }

        // Uniquement lettres, chiffres et underscores
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return false;
        }

        // Vérifier si le nom d'utilisateur est disponible
        return !User::where('name', $username)->exists();
    }
}
