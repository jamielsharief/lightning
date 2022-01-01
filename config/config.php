<?php
// Not using this right now
return [
    'app' => [
        'name' => env('APP_NAME', 'application'),
        'env' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false) === 'true',
        'url' => env('APP_URL', 'http://localhost'),
        // TODO: move to env
        'encoding' => 'UTF-8',
        'defaultTimezone' => 'UTC',
        'defaultLocale' => 'en_US',
    ],
    'session' => [
        'name' => 'id',
        'idLength' => 32, // Must be at least 128 bits (16 bytes)
        'timeout' => 900 // Logout after 15 minutes of in activity
    ],
    'database' => [
        'default' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'root',
            'database' => 'lightning'
        ],
        'test' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'root',
            'database' => 'lightning'
        ]
    ]
];
