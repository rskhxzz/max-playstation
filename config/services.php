<?php

return [
    'midtrans' => [
        'server_key'     => env('MIDTRANS_SERVER_KEY', ''),
        'client_key'     => env('MIDTRANS_CLIENT_KEY', ''),
        'is_production'  => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized'   => env('MIDTRANS_IS_SANITIZED', true),
        'is_3ds'         => env('MIDTRANS_IS_3DS', true),
        'notification_url' => env('MIDTRANS_NOTIFICATION_URL', ''),
    ],

    'google_maps' => [
        'browser_key' => env('GOOGLE_MAPS_BROWSER_KEY', ''),
        'server_key'  => env('GOOGLE_MAPS_SERVER_KEY', ''),
    ],
];
