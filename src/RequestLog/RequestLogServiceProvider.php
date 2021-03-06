<?php

namespace Nbj\RequestLog;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Nbj\RequestLog\Components\StatusCode;
use Nbj\RequestLog\Commands\InstallRequestLog;
use Nbj\RequestLog\Components\PrettyPrintJson;

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
            __DIR__ . '/../../publishable/config/request-log.php'                => config_path('request-log.php'),
            __DIR__ . '/../../publishable/models/RequestLog.php'                 => app_path('RequestLog.php'),
            __DIR__ . '/../../publishable/models/RequestLogBlacklistedRoute.php' => app_path('RequestLogBlacklistedRoute.php'),
            __DIR__ . '/../../publishable/middleware/LogRequest.php'             => app_path('/Http/Middleware/LogRequest.php'),
        ]);

        // Makes sure migrations and factories are added
        $this->loadMigrationsFrom(__DIR__ . '/../../publishable/migrations');
        $this->loadFactoriesFrom(__DIR__ . '/../../publishable/factories');

        // Make sure that routes are added
        $this->loadRoutesFrom(__DIR__ . '/../../publishable/routes/web.php');

        // Make sure that views and view-components are added
        $this->loadViewsFrom(__DIR__ . '/../../publishable/views', 'request-logs');
        $this->loadViewComponentsAs('request-log', [
            StatusCode::class,
            PrettyPrintJson::class
        ]);

        // Add the installation command to Artisan
        if ($this->app->runningInConsole()) {
            $this->commands([InstallRequestLog::class]);
        }

        // When this service provider is loaded in CI pipeline and using a
        // cache driver other than file, the service might not be available
        // To mitigate this we simply ignore the exception that is thrown
        $isEnabled = false;

        try {
            $isEnabled = Cache::get('request-log.enabled');

            if ($isEnabled === null) {
                $isEnabled = Config::get('request-log.enabled');
                Cache::set('request-log.enabled', $isEnabled);
            }
        } catch (Exception $exception) {
            Log::notice(sprintf('Cache driver is not available - Message: %s', $exception->getMessage()));
        }

        // Push Middleware to global middleware stack
        if ($isEnabled) {
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
        $this->app->singleton(\App\Http\Middleware\LogRequest::class);
    }
}
