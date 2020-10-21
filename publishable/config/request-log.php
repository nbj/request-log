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

    'logRetentionNumberOfDays' => 90,
];
