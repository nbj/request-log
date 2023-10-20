<?php

namespace Cego\RequestLog;

use Cego\FilebeatLoggerFactory;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Cego\RequestLog\Middleware\LogRequest;
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

        $this->pushGlobalMiddleware();

        if(class_exists(\Swoole\Http\Server::class) && $this->app->bound(\Swoole\Http\Server::class)) {
            $workerId = resolve(\Swoole\Http\Server::class)->worker_id;

            Config::set('logging.channels.request-logs', [
                'driver'   => 'custom',
                'channel'  => 'request-logs',
                'extras'   => json_decode(env('FILEBEAT_LOGGER_EXTRAS', '{}'), true, 512, JSON_THROW_ON_ERROR),
                'stream'   => sprintf('%s/request-log-%s.log', config('request-log.octane_log_folder'), $workerId),
                'rotating' => true,
                'via'      => FilebeatLoggerFactory::class,
            ]);
        }
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
}
