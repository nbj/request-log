<?php

namespace Nbj\RequestLog;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class RequestLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        // Publish resource to the project consuming this package
        $this->publishes([
            __DIR__ . '/Configs/request-log.php'   => config_path('request-log.php'),
            __DIR__ . '/Models/RequestLog.php'     => app_path('RequestLog.php'),
            __DIR__ . '/Middleware/LogRequest.php' => app_path('/Http/Middleware/LogRequest.php'),
        ]);

        // Makes sure migrations are added to the pool of the migrations for the project
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        if (config('request-log.enabled')) {
            $kernel = $this->app->make(Kernel::class);
            $kernel->pushMiddleware(\App\Http\Middleware\LogRequest::class);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('request-log.enabled')) {
            $this->app->singleton(\App\Http\Middleware\LogRequest::class);
        }
    }
}
