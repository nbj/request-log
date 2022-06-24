<?php

namespace Cego\RequestLog;

use Illuminate\Pagination\Paginator;
use Illuminate\Contracts\Http\Kernel;
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
        // Push Middleware to global middleware stack
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(LogRequest::class);
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
