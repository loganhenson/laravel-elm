<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Tightenco\Elm\Concerns\EnsureElmInitialized;

class Install extends Command
{
    use EnsureElmInitialized;

    protected $signature = 'elm:install {--force : Overwrite existing files}';
    protected $description = 'Install laravel-elm & tailwind / scaffold webpack.mix.js';

    public function handle()
    {
        $this->ensureInitialized();

        // Install NPM packages...
        $this->updateNodePackages(function ($packages) {
            return [
                "laravel-elm" => "^3.3.1",
                "tailwindcss" => "^1.8.2",
            ] + $packages;
        });

        $this->exportAppCss();
        $this->exportWebpackConfig();
        $this->exportTailwindConfig();

        $this->info('Laravel Elm fully installed & ready. Want an auth setup? Try `php artisan elm:auth`');
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
        copy(__DIR__ . '/../Fixtures/webpack.mix.js', base_path('webpack.mix.js'));
    }

    protected function exportTailwindConfig()
    {
        copy(__DIR__ . '/../Fixtures/tailwind.config.js', base_path('tailwind.config.js'));
    }

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
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

}
