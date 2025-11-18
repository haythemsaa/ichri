<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Loyalty Program Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the loyalty program behavior for ichri.tn
    |
    */

    'enabled' => env('LOYALTY_ENABLED', true),

    'points_per_tnd' => env('LOYALTY_POINTS_PER_TND', 1),

    'tiers' => [
        'bronze' => [
            'threshold' => 0,
            'multiplier' => 1.0,
            'benefits' => ['Points de base'],
        ],
        'silver' => [
            'threshold' => 1000,
            'multiplier' => 1.2,
            'benefits' => ['+20% de points sur chaque commande', 'Livraison prioritaire'],
        ],
        'gold' => [
            'threshold' => 5000,
            'multiplier' => 1.5,
            'benefits' => ['+50% de points', 'Support prioritaire', 'Offres exclusives'],
        ],
        'platinum' => [
            'threshold' => 15000,
            'multiplier' => 2.0,
            'benefits' => ['Double points (2x)', 'Account manager dédié', 'Offres VIP', 'Livraison gratuite'],
        ],
    ],

    'redemption' => [
        'expiry_days' => env('LOYALTY_REDEMPTION_EXPIRY_DAYS', 30),
        'min_points_to_redeem' => env('LOYALTY_MIN_POINTS_TO_REDEEM', 100),
    ],

    'point_expiry' => [
        'enabled' => env('LOYALTY_POINT_EXPIRY_ENABLED', false),
        'days' => env('LOYALTY_POINT_EXPIRY_DAYS', 365),
    ],

];
