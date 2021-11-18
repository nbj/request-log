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
    | Sets whether automatic log clean up is enabled
    */

    'automaticLogCleanUpEnabled' => true,

    /*
    | Sets the retention of logs in number of days
    */

    'logRetentionNumberOfDays' => 14,

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
