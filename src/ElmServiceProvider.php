<?php

namespace Tightenco\Elm;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Tightenco\Elm\Auth\AuthRouteMethods;
use Tightenco\Elm\Commands\Auth;
use Tightenco\Elm\Commands\Create;
use Tightenco\Elm\Commands\Install;
use Tightenco\Elm\Commands\Routes;
use Tightenco\Elm\Commands\SW;

class ElmServiceProvider extends ServiceProvider
{
    protected $commands = [
        Install::class,
        Create::class,
        Routes::class,
        SW::class,
        Auth::class,
    ];

    public function register()
    {
        $this->app->singleton('Elm', function ($app) {
            return new Elm;
        });

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    public function boot()
    {
        Blade::directive('elm', function () {
            try {
                $path = mix('/js/elm.min.js');
            } catch (\Exception $e) {
                $path = '/js/elm.js';
            }

            return "<script src=\"{$path}\"></script>" . '{!! $elm !!}';
        });

        Elm::share('errors', function () {
            return (object)[];
        });

        Elm::share('status', function () {
            return session()->has('status')
                ? session()
                    ->get('status')
                : null;
        });

        Route::mixin(new AuthRouteMethods);

        $this->app[Kernel::class]->pushMiddleware(Middleware::class);
    }
}
