<?php

namespace Cego\RequestLog;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Cego\RequestLog\Components\StatusCode;
use Cego\RequestLog\Middleware\LogRequest;
use Cego\RequestLog\Components\PrettyPrint;
use Illuminate\Console\Scheduling\Schedule;
use Cego\RequestLog\Commands\AutomaticLogCleanup;
use Illuminate\Contracts\Container\BindingResolutionException;

class RequestLogServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(LogRequest::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishAndLoadPackageComponents();

        $this->registerAndScheduleCommands();

        $this->pushGlobalMiddleware();

        $this->setPaginatorStyling();
    }

    /**
     * Publishes and loads package components so they are available to the application
     */
    protected function publishAndLoadPackageComponents(): void
    {
        // Publish resource to the project consuming this package
        $this->publishes([
            __DIR__ . '/../../publishable/config/request-log.php' => config_path('request-log.php'),
        ]);

        // Merge default config into the published
        // This allows for default values, and should autoload the config
        $this->mergeConfigFrom(
            __DIR__ . '/../../publishable/config/request-log.php',
            'request-log'
        );

        // Makes sure migrations and factories are added
        $this->loadMigrationsFrom(__DIR__ . '/../../publishable/migrations');

        // Make sure that routes are added
        $this->loadRoutesFrom(__DIR__ . '/../../publishable/routes/web.php');

        // Make sure that views and view-components are added
        $this->loadViewsFrom(__DIR__ . '/../../publishable/views', 'request-logs');
        $this->loadViewComponentsAs('request-log', [
            StatusCode::class,
            PrettyPrint::class
        ]);
    }

    /**
     * Registers all package commands, and schedules the required ones
     */
    protected function registerAndScheduleCommands(): void
    {
        // Only register and schedule commands if we are running in CLI mode
        if ( ! $this->app->runningInConsole()) {
            return;
        }

        // Register package commands
        $this->commands([
            AutomaticLogCleanup::class
        ]);

        // Automatic schedule package commands
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('clean:request-logs')->everyTenMinutes();
        });
    }

    /**
     * Pushes the RequestLog middleware to the global stack, if it is enabled
     *
     * @throws BindingResolutionException
     */
    protected function pushGlobalMiddleware(): void
    {
        // Only apply the middleware if the package is enabled
        if ($this->isRequestLogDisabled()) {
            return;
        }

        // Push Middleware to global middleware stack
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(LogRequest::class);
    }

    /**
     * Checks if Request Log is disabled or not
     *
     * @return bool
     */
    protected function isRequestLogDisabled(): bool
    {
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

        return ! $isEnabled;
    }

    /**
     * Sets the paginator styling to bootstrap if using Laravel 8
     */
    private function setPaginatorStyling(): void
    {
        // If Laravel version 8
        if (version_compare($this->app->version(), '8.0.0', '>=') === true) {
            // Use bootstrap for the paginator instead of tailwind, since the rest of the interface uses bootstrap
            Paginator::useBootstrap();
        }
    }
}
