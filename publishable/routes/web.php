<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Cego\RequestLog\Controllers')->prefix('vendor')->group(function () {
    Route::get('request-logs/toggle', 'RequestLogController@toggle')
        ->name('request-logs.toggle')
        ->middleware('web');

    Route::delete('request-logs/delete', 'RequestLogController@delete')
        ->name('request-logs.delete')
        ->middleware('web');

    Route::resource('request-logs/blacklisted-routes', 'BlacklistedRoutesController')
        ->only(['index', 'create', 'store', 'destroy'])
        ->middleware('web');

    Route::resource('request-logs', 'RequestLogController')
        ->only(['index', 'show', 'destroy'])
        ->middleware('web');
});
