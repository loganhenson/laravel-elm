<?php

namespace Tightenco\Elm;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ElmServiceProvider extends ServiceProvider
{
    protected $commands = [
        'Tightenco\Elm\Commands\Create',
        'Tightenco\Elm\Commands\Routes',
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
        Blade::directive('elm', function () {
            return '{!! $elm !!}';
        });

        Elm::share('errors', function () {
            return session()->has('errors')
                ? session()
                    ->get('errors')
                    ->getBag('default')
                    ->getMessages()
                : (object)[];
        });

        $this->app[Kernel::class]->pushMiddleware(Middleware::class);
    }
}
