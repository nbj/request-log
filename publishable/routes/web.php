<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Cego\RequestLog\Controllers')->prefix('vendor')
    ->group(function () {
        Route::get('request-logs/toggle', 'RequestLogController@toggle')
            ->name('request-logs.toggle');

        Route::delete('request-logs/delete', 'RequestLogController@delete')
            ->name('request-logs.delete');

        Route::resource('request-logs/blacklisted-routes', 'BlacklistedRoutesController')
            ->only(['index', 'create', 'store', 'destroy']);

        Route::resource('request-logs', 'RequestLogController')
            ->only(['index', 'show', 'destroy']);
    })->middleware([
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);
