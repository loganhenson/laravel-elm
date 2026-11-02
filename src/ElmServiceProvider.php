<?php

namespace LoganHenson\Elm;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use LoganHenson\Elm\Commands\Auth;
use LoganHenson\Elm\Commands\Create;
use LoganHenson\Elm\Commands\Install;
use LoganHenson\Elm\Commands\Routes;
use LoganHenson\Elm\Commands\SW;

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
        $this->app->singleton(ElmFacade::class);

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
