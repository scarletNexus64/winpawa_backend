<?php

namespace App\Services;

use App\Enums\GameType;

class GameImageGenerator
{
    private const THUMBNAIL_WIDTH = 400;
    private const THUMBNAIL_HEIGHT = 400;
    private const BANNER_WIDTH = 1200;
    private const BANNER_HEIGHT = 400;

    /**
     * Couleurs associées à chaque type de jeu
     */
    private function getGameColors(GameType $type): array
    {
        return match ($type) {
            GameType::ROULETTE => ['start' => [220, 20, 60], 'end' => [139, 0, 0]], // Rouge
            GameType::SCRATCH_CARD => ['start' => [255, 215, 0], 'end' => [218, 165, 32]], // Or
            GameType::COIN_FLIP => ['start' => [192, 192, 192], 'end' => [128, 128, 128]], // Argent
            GameType::DICE => ['start' => [255, 255, 255], 'end' => [200, 200, 200]], // Blanc
            GameType::ROCK_PAPER_SCISSORS => ['start' => [138, 43, 226], 'end' => [75, 0, 130]], // Violet
            GameType::TREASURE_BOX => ['start' => [255, 140, 0], 'end' => [184, 134, 11]], // Orange
            GameType::LUCKY_NUMBER => ['start' => [0, 191, 255], 'end' => [30, 144, 255]], // Bleu ciel
            GameType::JACKPOT => ['start' => [255, 215, 0], 'end' => [255, 165, 0]], // Or brillant
            GameType::PENALTY => ['start' => [50, 205, 50], 'end' => [34, 139, 34]], // Vert
            GameType::LUDO => ['start' => [255, 69, 0], 'end' => [220, 20, 60]], // Rouge-Orange
            GameType::QUIZ => ['start' => [147, 112, 219], 'end' => [106, 90, 205]], // Lavande
            GameType::COLOR_ROULETTE => ['start' => [255, 20, 147], 'end' => [199, 21, 133]], // Rose
        };
    }

    /**
     * Génère une vignette pour un type de jeu
     */
    public function generateThumbnail(GameType $type, string $gameName): string
    {
        $image = imagecreatetruecolor(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
        $colors = $this->getGameColors($type);

        // Créer un gradient
        $this->createGradient(
            $image,
            $colors['start'],
            $colors['end'],
            self::THUMBNAIL_WIDTH,
            self::THUMBNAIL_HEIGHT
        );

        // Ajouter l'icône emoji (grande taille)
        $icon = $type->icon();
        $iconColor = imagecolorallocate($image, 255, 255, 255);
        $fontSize = 120;
        $this->drawCenteredText($image, $icon, $fontSize, self::THUMBNAIL_WIDTH / 2, self::THUMBNAIL_HEIGHT / 2 - 40, $iconColor);

        // Ajouter le nom du jeu en dessous
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $this->drawCenteredText($image, $gameName, 24, self::THUMBNAIL_WIDTH / 2, self::THUMBNAIL_HEIGHT - 60, $textColor, true);

        // Sauvegarder l'image
        $filename = strtolower(str_replace(' ', '-', $type->value)) . '-thumbnail.png';
        $path = storage_path('app/public/games/thumbnails/' . $filename);
        imagepng($image, $path, 9);
        imagedestroy($image);

        return 'games/thumbnails/' . $filename;
    }

    /**
     * Génère une bannière pour un type de jeu
     */
    public function generateBanner(GameType $type, string $gameName, string $description): string
    {
        $image = imagecreatetruecolor(self::BANNER_WIDTH, self::BANNER_HEIGHT);
        $colors = $this->getGameColors($type);

        // Créer un gradient horizontal
        $this->createGradient(
            $image,
            $colors['start'],
            $colors['end'],
            self::BANNER_WIDTH,
            self::BANNER_HEIGHT,
            true
        );

        // Ajouter l'icône emoji à gauche
        $icon = $type->icon();
        $iconColor = imagecolorallocate($image, 255, 255, 255);
        $fontSize = 100;
        $this->drawText($image, $icon, $fontSize, 100, self::BANNER_HEIGHT / 2, $iconColor);

        // Ajouter le nom du jeu
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $this->drawText($image, $gameName, 48, 280, 150, $textColor, true);

        // Ajouter la description
        $descColor = imagecolorallocate($image, 240, 240, 240);
        $this->drawText($image, $description, 20, 280, 250, $descColor, true);

        // Sauvegarder l'image
        $filename = strtolower(str_replace(' ', '-', $type->value)) . '-banner.png';
        $path = storage_path('app/public/games/banners/' . $filename);
        imagepng($image, $path, 9);
        imagedestroy($image);

        return 'games/banners/' . $filename;
    }

    /**
     * Crée un gradient entre deux couleurs
     */
    private function createGradient($image, array $startColor, array $endColor, int $width, int $height, bool $horizontal = false): void
    {
        if ($horizontal) {
            for ($i = 0; $i < $width; $i++) {
                $r = $startColor[0] + ($endColor[0] - $startColor[0]) * $i / $width;
                $g = $startColor[1] + ($endColor[1] - $startColor[1]) * $i / $width;
                $b = $startColor[2] + ($endColor[2] - $startColor[2]) * $i / $width;
                $color = imagecolorallocate($image, (int)$r, (int)$g, (int)$b);
                imagefilledrectangle($image, $i, 0, $i, $height, $color);
            }
        } else {
            for ($i = 0; $i < $height; $i++) {
                $r = $startColor[0] + ($endColor[0] - $startColor[0]) * $i / $height;
                $g = $startColor[1] + ($endColor[1] - $startColor[1]) * $i / $height;
                $b = $startColor[2] + ($endColor[2] - $startColor[2]) * $i / $height;
                $color = imagecolorallocate($image, (int)$r, (int)$g, (int)$b);
                imagefilledrectangle($image, 0, $i, $width, $i, $color);
            }
        }
    }

    /**
     * Dessine du texte centré
     */
    private function drawCenteredText($image, string $text, int $fontSize, float $centerX, float $centerY, int $color, bool $bold = false): void
    {
        // Pour l'emoji, on utilise la taille directement
        if (preg_match('/[\x{1F300}-\x{1F9FF}]/u', $text)) {
            // C'est un emoji, on simule avec imagestring
            $x = $centerX - (strlen($text) * $fontSize / 4);
            $y = $centerY - ($fontSize / 2);
            imagestring($image, 5, (int)$x, (int)$y, $text, $color);
        } else {
            // Utiliser une police système si disponible
            $fontFile = $this->getSystemFont($bold);
            if ($fontFile && file_exists($fontFile)) {
                $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
                $textWidth = abs($bbox[4] - $bbox[0]);
                $textHeight = abs($bbox[5] - $bbox[1]);
                $x = $centerX - ($textWidth / 2);
                $y = $centerY + ($textHeight / 2);
                imagettftext($image, $fontSize, 0, (int)$x, (int)$y, $color, $fontFile, $text);
            } else {
                // Fallback sur imagestring
                $textWidth = strlen($text) * imagefontwidth(5);
                $textHeight = imagefontheight(5);
                $x = $centerX - ($textWidth / 2);
                $y = $centerY - ($textHeight / 2);
                imagestring($image, 5, (int)$x, (int)$y, $text, $color);
            }
        }
    }

    /**
     * Dessine du texte à une position donnée
     */
    private function drawText($image, string $text, int $fontSize, float $x, float $y, int $color, bool $bold = false): void
    {
        $fontFile = $this->getSystemFont($bold);
        if ($fontFile && file_exists($fontFile)) {
            imagettftext($image, $fontSize, 0, (int)$x, (int)$y, $color, $fontFile, $text);
        } else {
            imagestring($image, 5, (int)$x, (int)$y, $text, $color);
        }
    }

    /**
     * Obtient le chemin vers une police système
     */
    private function getSystemFont(bool $bold = false): ?string
    {
        $fonts = [
            '/System/Library/Fonts/Helvetica.ttc',
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/Windows/Fonts/arial.ttf',
            '/Windows/Fonts/arialbd.ttf',
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return null;
    }

    /**
     * Génère toutes les images pour tous les types de jeux
     */
    public function generateAllImages(): array
    {
        $games = [
            [
                'name' => 'Apple of Fortune',
                'type' => GameType::ROULETTE,
                'description' => 'Faites tourner la roue et gagnez !',
            ],
            [
                'name' => 'Cartes à Gratter',
                'type' => GameType::SCRATCH_CARD,
                'description' => 'Grattez et découvrez vos gains',
            ],
            [
                'name' => 'Pile ou Face',
                'type' => GameType::COIN_FLIP,
                'description' => 'Le classique ! Doublez votre mise',
            ],
            [
                'name' => 'Lancer de Dés',
                'type' => GameType::DICE,
                'description' => 'Pariez sur le nombre',
            ],
            [
                'name' => 'Pierre-Papier-Ciseaux',
                'type' => GameType::ROCK_PAPER_SCISSORS,
                'description' => 'Battez l\'ordinateur',
            ],
            [
                'name' => 'Coffre au Trésor',
                'type' => GameType::TREASURE_BOX,
                'description' => 'Choisissez le bon coffre',
            ],
            [
                'name' => 'Nombre Chanceux',
                'type' => GameType::LUCKY_NUMBER,
                'description' => 'Devinez le nombre entre 1 et 10',
            ],
            [
                'name' => 'Jackpot',
                'type' => GameType::JACKPOT,
                'description' => 'Tentez votre chance au jackpot',
            ],
            [
                'name' => 'Tir au But',
                'type' => GameType::PENALTY,
                'description' => 'Marquez et gagnez !',
            ],
            [
                'name' => 'Course de Pions',
                'type' => GameType::LUDO,
                'description' => 'Pariez sur le pion gagnant',
            ],
            [
                'name' => 'Quiz Chance',
                'type' => GameType::QUIZ,
                'description' => 'Répondez correctement',
            ],
            [
                'name' => 'Roulette Couleurs',
                'type' => GameType::COLOR_ROULETTE,
                'description' => 'Rouge, Bleu, Vert ou Jaune ?',
            ],
        ];

        $generated = [];

        foreach ($games as $game) {
            $thumbnail = $this->generateThumbnail($game['type'], $game['name']);
            $banner = $this->generateBanner($game['type'], $game['name'], $game['description']);

            $generated[] = [
                'name' => $game['name'],
                'type' => $game['type']->value,
                'thumbnail' => $thumbnail,
                'banner' => $banner,
            ];
        }

        return $generated;
    }
}
