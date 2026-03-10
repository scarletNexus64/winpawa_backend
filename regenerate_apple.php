<?php

/**
 * Script pour régénérer les segments d'Apple of Fortune
 * avec la nouvelle distribution corrigée
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🎡 Régénération des segments Apple of Fortune...\n\n";

// Récupérer le jeu Apple of Fortune
$game = DB::table('games')
    ->where('slug', 'apple-of-fortune')
    ->first();

if (!$game) {
    echo "❌ Erreur: Le jeu 'Apple of Fortune' n'existe pas.\n";
    exit(1);
}

echo "📊 Configuration actuelle:\n";
echo "   - Win Frequency: {$game->win_frequency}%\n";
echo "   - Multipliers: " . json_encode(json_decode($game->multipliers)) . "\n";

// Décoder les settings actuels
$settings = json_decode($game->settings, true) ?? [];
$currentPrizes = $settings['prizes'] ?? [];

echo "   - Segments actuels: " . count($currentPrizes) . "\n";

// Compter les segments gagnants
$winningCount = 0;
foreach ($currentPrizes as $prize) {
    if (isset($prize['multiplier']) && $prize['multiplier'] > 0) {
        $winningCount++;
    }
}

$actualWinRate = count($currentPrizes) > 0 ? ($winningCount / count($currentPrizes)) * 100 : 0;
echo "   - Taux de gain ACTUEL: " . number_format($actualWinRate, 2) . "%\n\n";

// Générer les nouveaux segments avec la méthode corrigée
$multipliers = json_decode($game->multipliers, true);
$winFrequency = (float) $game->win_frequency;

echo "🔧 Génération des nouveaux segments...\n";

// Utiliser la même logique que Game.php (version corrigée)
$winningColors = ['#FF6B6B', '#4ECDC4', '#FFD93D', '#95E1D3', '#F38181', '#AA96DA'];
$losingColors = ['#2C3E50', '#34495E', '#1C2833', '#17202A', '#212F3C', '#273746'];

$winningCount = count($multipliers);

// Calculer avec floor() pour favoriser le joueur
$totalSegments = $winFrequency > 0 ? floor($winningCount / ($winFrequency / 100)) : $winningCount * 2;

if ($totalSegments < $winningCount) {
    $totalSegments = $winningCount;
}

$losingCount = max(0, $totalSegments - $winningCount);

echo "   - Segments gagnants: $winningCount\n";
echo "   - Segments perdants: $losingCount\n";
echo "   - Total segments: $totalSegments\n";

$newWinRate = ($winningCount / $totalSegments) * 100;
echo "   - Nouveau taux de gain: " . number_format($newWinRate, 2) . "%\n\n";

// Créer les segments gagnants
$winningSegments = [];
foreach ($multipliers as $index => $multiplier) {
    $winningSegments[] = [
        'multiplier' => $multiplier,
        'color' => $winningColors[$index % count($winningColors)],
        'is_winner' => true,
    ];
}

// Créer les segments perdants
$losingSegments = [];
for ($i = 0; $i < $losingCount; $i++) {
    $losingSegments[] = [
        'multiplier' => 0,
        'color' => $losingColors[$i % count($losingColors)],
        'is_winner' => false,
    ];
}

// Distribuer intelligemment les segments
$prizes = [];
$segmentNumber = 1;

$spacing = $losingCount > 0 ? floor($losingCount / $winningCount) : 0;
$remainingLosers = $losingCount;

$winIndex = 0;
$loseIndex = 0;

while ($winIndex < $winningCount || $loseIndex < $losingCount) {
    if ($winIndex < $winningCount) {
        $prizes[$segmentNumber] = $winningSegments[$winIndex];
        $segmentNumber++;
        $winIndex++;

        $losersToAdd = $remainingLosers > 0 && $winningCount > 0
            ? min($spacing + ($remainingLosers % ($winningCount - $winIndex + 1) > 0 ? 1 : 0), $remainingLosers)
            : 0;

        for ($i = 0; $i < $losersToAdd && $loseIndex < $losingCount; $i++) {
            $prizes[$segmentNumber] = $losingSegments[$loseIndex];
            $segmentNumber++;
            $loseIndex++;
            $remainingLosers--;
        }
    }
}

while ($loseIndex < $losingCount) {
    $prizes[$segmentNumber] = $losingSegments[$loseIndex];
    $segmentNumber++;
    $loseIndex++;
}

// Afficher la distribution
echo "📋 Distribution des segments:\n";
foreach ($prizes as $num => $prize) {
    $type = $prize['multiplier'] > 0 ? "GAGNANT ({$prize['multiplier']}x)" : "PERDANT (0x)";
    $color = $prize['color'];
    echo "   Segment #$num: $type - $color\n";
}

// Mettre à jour les settings
$settings['prizes'] = $prizes;
$settings['segments'] = count($prizes);
$settings['winning_segments'] = $winningCount;
$settings['win_frequency'] = $winFrequency;

// Sauvegarder dans la base de données
DB::table('games')
    ->where('id', $game->id)
    ->update([
        'settings' => json_encode($settings),
        'updated_at' => now(),
    ]);

echo "\n✅ Segments régénérés avec succès!\n";
echo "🎮 Vous pouvez maintenant tester le jeu.\n";
