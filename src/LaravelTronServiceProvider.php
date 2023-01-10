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
        $this->app->bind('Tron', function ($app) {
            return new Tron();
        });
    }

    /**
     * Publish the plugin configuration.
     */
    public function boot()
    {
        //
    }
}
