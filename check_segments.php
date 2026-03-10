<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Récupérer le jeu Apple of Fortune
$game = DB::table('games')
    ->where('slug', 'apple-of-fortune')
    ->first();

if (!$game) {
    echo "❌ Erreur: Le jeu 'Apple of Fortune' n'existe pas.\n";
    exit(1);
}

$settings = json_decode($game->settings, true);
$prizes = $settings['prizes'] ?? [];

echo "🎡 Segments Apple of Fortune (ordre exact):\n\n";

foreach ($prizes as $num => $prize) {
    $type = $prize['multiplier'] > 0 ? "WIN {$prize['multiplier']}x" : "LOSE 0x";
    $color = $prize['color'];
    $emoji = $prize['multiplier'] > 0 ? '🎉' : '💀';

    echo "Segment #$num: $emoji $type - $color\n";
}

echo "\n";
echo "Total segments: " . count($prizes) . "\n";
echo "Segment angle: " . (360 / count($prizes)) . "°\n";
