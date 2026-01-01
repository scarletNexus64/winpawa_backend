<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration WINPAWA
    |--------------------------------------------------------------------------
    */

    'app_name' => env('APP_NAME', 'WINPAWA'),
    'currency' => env('CURRENCY', 'XAF'),
    'country' => env('COUNTRY', 'CM'),

    /*
    |--------------------------------------------------------------------------
    | Dépôts et Retraits
    |--------------------------------------------------------------------------
    */
    'deposit' => [
        'minimum' => env('MIN_DEPOSIT', 200),
        'maximum' => env('MAX_DEPOSIT', 1000000),
    ],

    'withdrawal' => [
        'minimum' => env('MIN_WITHDRAWAL', 1000),
        'maximum' => env('MAX_WITHDRAWAL', 500000),
        'daily_limit' => env('DAILY_WITHDRAWAL_LIMIT', 2000000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Bonus
    |--------------------------------------------------------------------------
    */
    'bonus' => [
        'signup_percentage' => env('SIGNUP_BONUS_PERCENTAGE', 50),
        'wagering_requirement' => env('SIGNUP_BONUS_WAGERING_REQUIREMENT', 5),
        'expiry_days' => env('BONUS_EXPIRY_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Affiliation
    |--------------------------------------------------------------------------
    */
    'affiliate' => [
        'deposit_commission' => env('AFFILIATE_DEPOSIT_COMMISSION', 5),
        'loss_commission' => env('AFFILIATE_LOSS_COMMISSION', 25),
        'min_withdrawal' => env('AFFILIATE_MIN_WITHDRAWAL', 5000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration RNG (Random Number Generator)
    |--------------------------------------------------------------------------
    */
    'rng' => [
        'default_rtp' => env('RNG_DEFAULT_RTP', 75),
        'default_win_frequency' => env('RNG_DEFAULT_WIN_FREQUENCY', 35),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Virtual Match
    |--------------------------------------------------------------------------
    */
    'virtual_match' => [
        'enabled' => env('VIRTUAL_MATCH_ENABLED', true),
        'match_interval' => env('VIRTUAL_MATCH_INTERVAL', 5), // minutes
        'default_duration' => env('VIRTUAL_MATCH_DURATION', 3), // minutes
        'betting_cutoff' => 10, // secondes avant le match
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites de mise par jeu
    |--------------------------------------------------------------------------
    */
    'betting' => [
        'default_min' => 100,
        'default_max' => 100000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration Mobile Money
    |--------------------------------------------------------------------------
    */
    'mtn_momo' => [
        'api_key' => env('MTN_MOMO_API_KEY'),
        'api_secret' => env('MTN_MOMO_API_SECRET'),
        'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
        'environment' => env('MTN_MOMO_ENVIRONMENT', 'sandbox'),
        'callback_url' => env('MTN_MOMO_CALLBACK_URL'),
    ],

    'orange_money' => [
        'api_key' => env('ORANGE_MONEY_API_KEY'),
        'api_secret' => env('ORANGE_MONEY_API_SECRET'),
        'merchant_key' => env('ORANGE_MONEY_MERCHANT_KEY'),
        'environment' => env('ORANGE_MONEY_ENVIRONMENT', 'sandbox'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sécurité Anti-Fraude
    |--------------------------------------------------------------------------
    */
    'security' => [
        'max_bets_per_minute' => 10,
        'max_deposit_per_day' => 5000000,
        'suspicious_win_threshold' => 10, // gains consécutifs
        'ip_rate_limit' => 100, // requêtes par minute
    ],
];
