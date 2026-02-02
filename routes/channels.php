<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel public pour tous les matchs virtuels
Broadcast::channel('virtual-matches', function () {
    return true;
});

// Channel public pour un match virtuel spécifique
Broadcast::channel('virtual-match.{id}', function ($user, $id) {
    return true;
});
