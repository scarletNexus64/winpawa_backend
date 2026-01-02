<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\VirtualMatchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - WINPAWA Casino Platform
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Public categories list
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category:slug}', [CategoryController::class, 'show']);

// Public games list
Route::get('games', [GameController::class, 'index']);
Route::get('games/featured', [GameController::class, 'featured']);
Route::get('games/{game:slug}', [GameController::class, 'show']);

// Public sports routes
Route::prefix('sports')->group(function () {
    Route::get('categories', [SportController::class, 'categories']);
    Route::get('', [SportController::class, 'sports']);
    Route::get('matches', [SportController::class, 'matches']);
    Route::get('matches/live', [SportController::class, 'liveMatches']);
    Route::get('{sportSlug}/matches', [SportController::class, 'matches']);
});

// Virtual Match public
Route::prefix('virtual-match')->group(function () {
    Route::get('upcoming', [VirtualMatchController::class, 'upcoming']);
    Route::get('live', [VirtualMatchController::class, 'live']);
    Route::get('results', [VirtualMatchController::class, 'results']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // Games
    Route::prefix('games')->group(function () {
        Route::post('{game:slug}/play', [GameController::class, 'play']);
        Route::get('history', [GameController::class, 'history']);
    });

    // Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('balance', [WalletController::class, 'balance']);
        Route::post('deposit', [WalletController::class, 'deposit']);
        Route::post('withdraw', [WalletController::class, 'withdraw']);
        Route::get('transactions', [WalletController::class, 'transactions']);
        Route::post('claim-bonus', [WalletController::class, 'claimBonus']);
    });

    // Affiliate
    Route::prefix('affiliate')->group(function () {
        Route::get('stats', [AffiliateController::class, 'stats']);
        Route::get('referrals', [AffiliateController::class, 'referrals']);
        Route::get('commissions', [AffiliateController::class, 'commissions']);
        Route::post('withdraw', [AffiliateController::class, 'withdraw']);
    });

    // Virtual Match
    Route::prefix('virtual-match')->group(function () {
        Route::post('{virtualMatch}/bet', [VirtualMatchController::class, 'placeBet']);
        Route::get('my-bets', [VirtualMatchController::class, 'myBets']);
    });
});

// Webhooks (no auth, but verified by signature)
Route::prefix('webhooks')->group(function () {
    Route::post('mtn-momo', [WalletController::class, 'mtnCallback']);
    Route::post('orange-money', [WalletController::class, 'orangeCallback']);
});
