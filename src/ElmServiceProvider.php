<?php

namespace Tightenco\Elm;

use Illuminate\Support\ServiceProvider;

class ElmServiceProvider extends ServiceProvider
{
    protected $commands = [
        'Tightenco\Elm\Commands\Create',
    ];

    public function register()
    {
        $this->app->singleton('Elm', function ($app) {
            return new Elm;
        });

        $this->commands($this->commands);
    }

    public function boot()
    {
        //
    }
}
