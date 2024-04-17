<?php

namespace Programic\HttpLogger;

use Illuminate\Support\ServiceProvider;
use Programic\HttpLogger\Contracts\LogProfile;
use Programic\HttpLogger\Contracts\LogWriter;
use Programic\HttpLogger\Middlewares\HttpLogger;
use Programic\HttpLogger\Contracts\HttpRequest as HttpLoggerContract;

class HttpLoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_http_requests_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_http_requests_table.php'),
            ], 'migrations');

            $this->publishes([
                __DIR__.'/../config/http-logger.php' => config_path('http-logger.php'),
            ], 'config');
        }

        $this->app->singleton(LogProfile::class, config('http-logger.log_profile'));
        $this->app->singleton(LogWriter::class, config('http-logger.log_writer'));
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/http-logger.php', 'http-logger');

        $this->app->singleton(HttpLogger::class);
        $this->app->bind(HttpLoggerContract::class, fn ($app) => $app->make($app->config['http-logger.database_log_model']));
    }
}
