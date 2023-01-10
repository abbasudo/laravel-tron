<?php

namespace Llabbasmkhll\LaravelTron;

use Illuminate\Support\ServiceProvider;

class LaravelTronServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Tron', function ($app) {
            return new Tron();
        });
    }

    /**
     * Publish the plugin configuration.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tron.php' => config_path('tron.php'),
            ], 'tron-config');
        }
    }
}
