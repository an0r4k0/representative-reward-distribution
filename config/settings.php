<?php

return [
    'management' => [
        'address' => env('MANAGEMENT_ADDRESS', ''),
        'percentage' => env('MANAGEMENT_PERCENTAGE', 20),
    ],

    'delegator' => [
        'min_balance' => env('DELEGATOR_MIN_BALANCE', 1),
        'sampling_interval' => env('DELEGATOR_SAMPLING_INTERVAL', 5),
    ],

    'node' => [
        'wallet_id' => env('WALLET_ID', ''),
        'address' => env('ADDRESS', ''),
        'rpc' => env('NODE_RPC', 'http://127.0.0.1:7076'),
    ],

    'timezone' => env('TIMEZONE', 'UTC'),

    'directory' => $_SERVER['HOME'] . '/raione/',
];
