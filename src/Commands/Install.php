<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Tightenco\Elm\Concerns\EnsureElmInitialized;

class Install extends Command
{
    use EnsureElmInitialized;

    protected $signature = 'elm:install {--force : Overwrite existing files}';
    protected $description = 'Install laravel-elm & tailwind / scaffold webpack.mix.js';

    /**
     * Credit: https://github.com/laravel/jetstream
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }

    public function handle()
    {
        $this->ensureInitialized(true);

        // Install NPM packages...
        $this->updateNodePackages(function ($packages) {
            return [
                    "laravel-elm" => "^3.0.0",
                    "tailwindcss" => "^2.0.0",
                ] + $packages;
        });

        $this->exportAppBladeView();
        $this->exportAppCss();
        $this->exportWebpackConfig();
        $this->exportTailwindConfig();

        $this->info('Laravel Elm fully installed & ready. Want an auth setup? Try `php artisan elm:auth`');
    }

    protected function exportAppBladeView()
    {
        $view = resource_path('views/app.blade.php');
        $putView = function () use ($view) {
            file_put_contents($view, file_get_contents(__DIR__ . '/../Fixtures/app.blade.php'));
        };

        if (file_exists($view) && ! $this->option('force')) {
            if ($this->confirm("The [app.blade.php] file already exists. Do you want to replace it?")) {
                $putView();
            }
        } else {
            $putView();
        }
    }

    protected function exportAppCss()
    {
        file_put_contents(
            resource_path('css/app.css'),
            file_get_contents(__DIR__ . '/../Fixtures/css/app.css'),
            FILE_APPEND
        );
    }

    protected function exportWebpackConfig()
    {
        $webpackMixJs = base_path('webpack.mix.js');
        $putWebpackMixJs = function () use ($webpackMixJs) {
            copy(__DIR__ . '/../Fixtures/webpack.mix.js', base_path('webpack.mix.js'));
        };

        if (file_exists($webpackMixJs) && ! $this->option('force')) {
            if ($this->confirm("The [webpack.mix.js] file already exists. Do you want to replace it?")) {
                $putWebpackMixJs();
            }
        } else {
            $putWebpackMixJs();
        }
    }

    protected function exportTailwindConfig()
    {
        $tailwindConfig = base_path('tailwind.config.js');
        $putTailwindConfig = function () use ($tailwindConfig) {
            copy(__DIR__ . '/../Fixtures/tailwind.config.js', base_path('tailwind.config.js'));
        };

        if (file_exists($tailwindConfig) && ! $this->option('force')) {
            if ($this->confirm("The [tailwind.config.js] file already exists. Do you want to replace it?")) {
                $putTailwindConfig();
            }
        } else {
            $putTailwindConfig();
        }
    }
}
