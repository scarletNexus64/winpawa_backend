<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ==================== MISE À JOUR AUTOMATIQUE DES MATCHS VIRTUELS ====================
// Vérifie toutes les minutes si des matchs doivent changer de statut
Schedule::command('virtual-match:update-status')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
