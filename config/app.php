<?php
declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'MessengerBackend'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'jwt' => [
        'secret' => env('JWT_SECRET', 'change-me'),
        'expiry' => (int) env('JWT_EXPIRY', 86400),
    ],
    'rate_limit' => [
        'max' => (int) env('RATE_LIMIT_MAX', 60),
        'window' => (int) env('RATE_LIMIT_WINDOW', 60),
    ],
];
