<?php

use App\Models\AuthUser;

return [
    'defaults' => [
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // Guard terpisah untuk Admin — session key: login_admin_HASH
        'admin' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // Guard terpisah untuk Driver — session key: login_driver_HASH
        'driver' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        // Satu provider cukup — keduanya pakai model AuthUser yang sama
        'users' => [
            'driver' => 'eloquent',
            'model'  => AuthUser::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
