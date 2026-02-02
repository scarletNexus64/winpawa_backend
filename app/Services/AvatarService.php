<?php

namespace App\Services;

class AvatarService
{
    /**
     * Couleurs de fond disponibles pour les avatars
     */
    private static array $colors = [
        '3B82F6', // Blue
        '10B981', // Green
        'F59E0B', // Amber
        'EF4444', // Red
        '8B5CF6', // Purple
        'EC4899', // Pink
        '06B6D4', // Cyan
        'F97316', // Orange
        '14B8A6', // Teal
        '6366F1', // Indigo
        'A855F7', // Purple
        'F43F5E', // Rose
    ];

    /**
     * Génère une URL d'avatar basée sur le nom d'utilisateur
     * Utilise UI Avatars (https://ui-avatars.com/)
     */
    public static function generate(string $name): string
    {
        // Extraire les initiales (2 premières lettres du nom)
        $initials = self::getInitials($name);

        // Choisir une couleur aléatoire basée sur le nom (pour consistance)
        $color = self::getColorForName($name);

        // Construire l'URL de l'avatar
        return "https://ui-avatars.com/api/?name=" . urlencode($initials)
            . "&background=" . $color
            . "&color=fff"
            . "&size=200"
            . "&bold=true"
            . "&format=png";
    }

    /**
     * Génère un avatar local en base64 (SVG)
     * Alternative si on ne veut pas dépendre d'un service externe
     */
    public static function generateLocal(string $name): string
    {
        $initials = self::getInitials($name);
        $color = self::getColorForName($name);

        $svg = <<<SVG
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
    <rect width="200" height="200" fill="#{$color}"/>
    <text x="50%" y="50%" text-anchor="middle" dy=".35em" font-family="Arial, sans-serif" font-size="80" font-weight="bold" fill="#ffffff">
        {$initials}
    </text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Extrait les initiales d'un nom
     */
    private static function getInitials(string $name): string
    {
        // Nettoyer le nom
        $name = trim($name);

        // Si le nom contient des espaces, prendre la première lettre de chaque mot
        if (str_contains($name, ' ')) {
            $parts = explode(' ', $name);
            $initials = '';
            foreach (array_slice($parts, 0, 2) as $part) {
                if (!empty($part)) {
                    $initials .= strtoupper(mb_substr($part, 0, 1));
                }
            }
            return $initials;
        }

        // Sinon, prendre les 2 premières lettres
        return strtoupper(mb_substr($name, 0, 2));
    }

    /**
     * Retourne une couleur basée sur le nom (pour consistance)
     */
    private static function getColorForName(string $name): string
    {
        // Utiliser un hash du nom pour choisir une couleur de manière déterministe
        $hash = crc32($name);
        $index = $hash % count(self::$colors);
        return self::$colors[$index];
    }

    /**
     * Vérifie si une URL est un avatar par défaut
     */
    public static function isDefaultAvatar(?string $avatar): bool
    {
        if (empty($avatar)) {
            return true;
        }

        return str_contains($avatar, 'ui-avatars.com') ||
               str_contains($avatar, 'data:image/svg+xml');
    }

    /**
     * Génère un avatar de secours si l'URL est invalide
     */
    public static function getFallbackAvatar(string $name): string
    {
        return self::generate($name);
    }
}
