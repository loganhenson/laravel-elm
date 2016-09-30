<?php

namespace Tighten\Elm;

use Illuminate\Support\ServiceProvider;

/**
 * Class ElmProvider
 * @package Tighten\Elm
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
        class_alias('Tighten\Elm\ElmFacade', 'Elm');
    }
}
