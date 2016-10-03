<?php

namespace Tightenco\Elm;

use Illuminate\Support\ServiceProvider;

/**
 * Class ElmServiceProvider
 * @package Tightenco\Elm
 */
class ElmServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Elm', function ($app) {
            return new Elm;
        });
    }

    /**
     * Publish the plugin configuration.
     */
    public function boot()
    {
        class_alias('Tightenco\Elm\ElmFacade', 'Elm');
    }
}
