<?php

namespace LoganHenson\Elm\Commands;

use Illuminate\Console\Command;
use LoganHenson\Elm\Concerns\EnsureElmInitialized;

class Install extends Command
{
    use EnsureElmInitialized;

    protected $signature = 'elm:install {--force : Overwrite existing files}';
    protected $description = 'Install laravel-elm & tailwind / scaffold vite.config.js';

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
                'laravel-elm' => '^4.1.0',
                'laravel-vite-plugin' => '^3.1',
                'tailwindcss' => '^4.3.0',
                '@tailwindcss/vite' => '^4.3.0',
                'vite' => '^8.0.0',
            ] + $packages;
        });

        $this->publish(__DIR__ . '/../Fixtures/app.blade.php', resource_path('views/app.blade.php'));
        $this->publish(__DIR__ . '/../Fixtures/css/app.css', resource_path('css/app.css'));
        $this->publish(__DIR__ . '/../Fixtures/vite.config.js', base_path('vite.config.js'));

        $this->info('Laravel Elm installed. Run `npm install`, then `npm run dev`.');
        $this->info('Want an auth setup? Try `php artisan elm:auth`');
    }

    protected function publish(string $from, string $to)
    {
        $name = basename($to);

        if (file_exists($to) && ! $this->option('force')
            && ! $this->confirm("The [{$name}] file already exists. Do you want to replace it?")) {
            return;
        }

        if (! is_dir(dirname($to))) {
            mkdir(dirname($to), 0755, true);
        }

        copy($from, $to);
    }
}
