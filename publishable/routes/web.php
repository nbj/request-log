<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Nbj\RequestLog\Controllers')->group(function () {
    Route::resource("request-logs", "RequestLogController")
        ->only(["index", "show"])
        ->middleware('web');
});
