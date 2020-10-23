<?php

namespace Cego\RequestLog;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Cego\RequestLog\Middleware\LogRequest;
use Cego\RequestLog\Components\StatusCode;
use Illuminate\Console\Scheduling\Schedule;
use Cego\RequestLog\Components\PrettyPrintJson;
use Cego\RequestLog\Commands\AutomaticLogCleanup;

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
            __DIR__ . '/../../publishable/config/request-log.php' => config_path('request-log.php'),
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
            $this->commands([
                AutomaticLogCleanup::class
            ]);
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

        if ($isEnabled) {
            // Push Middleware to global middleware stack
            $kernel = $this->app->make(Kernel::class);
            $kernel->pushMiddleware(LogRequest::class);

            // Add automatic clean up to scheduler
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                $schedule->command('clean:request-logs')->dailyAt('03:00');
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LogRequest::class);
    }
}
