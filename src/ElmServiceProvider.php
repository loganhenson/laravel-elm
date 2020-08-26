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
            try {
                $path = mix('/js/elm.js');
            } catch (\Exception $e) {
                $path = '/js/elm.js';
            }

            return "<script src=\"{$path}\"></script>" . '{!! $elm !!}';
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
