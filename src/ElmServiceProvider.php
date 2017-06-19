<?php

namespace Tightenco\Elm;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

/**
 * Class ElmServiceProvider
 * @package Tightenco\Elm
 */
class ElmServiceProvider extends ServiceProvider
{
    protected $commands = [
        'Tightenco\Elm\Commands\Create'
    ];

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

        $this->commands($this->commands);
    }

    /**
     * Publish the plugin configuration.
     */
    public function boot()
    {
        //
    }
}
