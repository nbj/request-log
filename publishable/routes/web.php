<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken;

Route::namespace('Cego\RequestLog\Controllers')
    ->prefix('vendor')
    ->middleware('web')
    ->group(function () {
        Route::get('request-logs/toggle', 'RequestLogController@toggle')
            ->name('request-logs.toggle')
            ->withoutMiddleware(VerifyCsrfToken::class);

        Route::delete('request-logs/delete', 'RequestLogController@delete')
            ->name('request-logs.delete')
            ->withoutMiddleware(VerifyCsrfToken::class);

        Route::resource('request-logs/blacklisted-routes', 'BlacklistedRoutesController')
            ->only(['index', 'create', 'store', 'destroy'])
            ->withoutMiddleware(VerifyCsrfToken::class);

        Route::resource('request-logs', 'RequestLogController')
            ->only(['index', 'show', 'destroy'])
            ->withoutMiddleware(VerifyCsrfToken::class);
    });
