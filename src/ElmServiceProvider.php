<?php

namespace Tightenco\Elm;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
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

        AliasLoader::getInstance()->alias('Elm', Elm::class);

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    public function boot()
    {
        Blade::directive('elm', function ($expression) {
            $path = $expression ?: "'resources/js/elm.js'";
            return "<?php echo app(\Illuminate\Foundation\Vite::class)({$path}); ?>" . '{!! $elm !!}';
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

        $this->app[Kernel::class]->pushMiddleware(Middleware::class);
    }
}
