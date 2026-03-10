<?php

/**
 * Script pour tester différents taux de win_frequency
 * et voir comment les segments sont générés
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🎡 TEST DE WIN_FREQUENCY DYNAMIQUE\n";
echo "====================================\n\n";

// Demander le nouveau taux
echo "Entrez le nouveau win_frequency (en %, ex: 20): ";
$handle = fopen("php://stdin", "r");
$newWinFrequency = trim(fgets($handle));
fclose($handle);

if (!is_numeric($newWinFrequency) || $newWinFrequency <= 0 || $newWinFrequency > 100) {
    echo "❌ Erreur: Veuillez entrer un nombre entre 0 et 100.\n";
    exit(1);
}

$newWinFrequency = (float) $newWinFrequency;

echo "\n🔧 Application du nouveau taux: {$newWinFrequency}%\n\n";

// Mettre à jour le jeu
$game = DB::table('games')
    ->where('slug', 'apple-of-fortune')
    ->first();

if (!$game) {
    echo "❌ Erreur: Le jeu 'Apple of Fortune' n'existe pas.\n";
    exit(1);
}

// Mettre à jour le win_frequency
DB::table('games')
    ->where('id', $game->id)
    ->update([
        'win_frequency' => $newWinFrequency,
        'updated_at' => now(),
    ]);

echo "✅ Win frequency mis à jour: {$newWinFrequency}%\n\n";

// Le hook boot() de Game.php va automatiquement régénérer les segments
// Mais comme on utilise DB::table(), il faut le faire manuellement
$multipliers = json_decode($game->multipliers, true);
$winningCount = count($multipliers);

// Calculer le nombre de segments
$totalSegments = $newWinFrequency > 0 ? floor($winningCount / ($newWinFrequency / 100)) : $winningCount * 2;

if ($totalSegments < $winningCount) {
    $totalSegments = $winningCount;
}

$losingCount = max(0, $totalSegments - $winningCount);
$actualWinRate = ($winningCount / $totalSegments) * 100;

echo "📊 CALCUL:\n";
echo "   - Multiplicateurs: " . json_encode($multipliers) . "\n";
echo "   - Segments gagnants: $winningCount\n";
echo "   - Segments perdants: $losingCount\n";
echo "   - Total segments: $totalSegments\n";
echo "   - Angle par segment: " . (360 / $totalSegments) . "°\n";
echo "   - Taux de gain RÉEL: " . number_format($actualWinRate, 2) . "%\n\n";

// Régénérer les segments
$winningColors = ['#FF6B6B', '#4ECDC4', '#FFD93D', '#95E1D3', '#F38181', '#AA96DA'];
$losingColors = ['#2C3E50', '#34495E', '#1C2833', '#17202A', '#212F3C', '#273746'];

$winningSegments = [];
foreach ($multipliers as $index => $multiplier) {
    $winningSegments[] = [
        'multiplier' => $multiplier,
        'color' => $winningColors[$index % count($winningColors)],
        'is_winner' => true,
    ];
}

$losingSegments = [];
for ($i = 0; $i < $losingCount; $i++) {
    $losingSegments[] = [
        'multiplier' => 0,
        'color' => $losingColors[$i % count($losingColors)],
        'is_winner' => false,
    ];
}

// Distribuer intelligemment
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
echo "🎯 DISTRIBUTION DES SEGMENTS:\n";
$winCount = 0;
foreach ($prizes as $num => $prize) {
    $type = $prize['multiplier'] > 0 ? "WIN ({$prize['multiplier']}x)" : "LOSE (0x)";
    $emoji = $prize['multiplier'] > 0 ? '🎉' : '💀';
    $probability = number_format((1 / $totalSegments) * 100, 2);

    echo sprintf("   Segment #%-2d: %s %-12s | Couleur: %-10s | Prob: %s%%\n",
        $num, $emoji, $type, $prize['color'], $probability);

    if ($prize['multiplier'] > 0) {
        $winCount++;
    }
}

// Sauvegarder dans la base
$settings = json_decode($game->settings, true) ?? [];
$settings['prizes'] = $prizes;
$settings['segments'] = count($prizes);
$settings['winning_segments'] = $winningCount;
$settings['win_frequency'] = $newWinFrequency;

DB::table('games')
    ->where('id', $game->id)
    ->update([
        'settings' => json_encode($settings),
        'updated_at' => now(),
    ]);

echo "\n✅ Segments sauvegardés dans la base de données!\n";
echo "🎮 Rechargez votre frontend pour voir les changements.\n";
