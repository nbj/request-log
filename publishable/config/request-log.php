<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Request logging
    |--------------------------------------------------------------------------
    |
    | Here you can enable if the application should log each incoming request
    | Logs can be found in the request_logs table
    |
    */

    'enabled' => env('REQUEST_LOG_ENABLED', false),

    /*
    | Set of headers and query parameters to redact from the log
    */

    'redact' => [
        'query' => [
            'token',
            'authToken',
            'auth',
            'authorization',
        ],

        'headers' => [
            'Authorization',
            'php-auth-pw',
            'php-auth-user',
            'cf-access-jwt-assertion',
        ],
        'cookies' => [
            'cegosso',
        ]
    ],

    /*
    | A list of default routes that are always black listed
    */

    'blackListedRoutes' => [
        'livewire/*',       // Example: livewire/livewire.js
        'vendor/*',         // Example: vendor/request-logs
        'vendor/*/*',       // Example: vendor/request-logs/1
        'vendor/*/*/*',     // Example: Vendor/request-logs/1/delete
    ],
];
