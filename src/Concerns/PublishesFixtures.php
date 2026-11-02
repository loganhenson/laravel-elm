<?php

namespace LoganHenson\Elm\Concerns;

trait PublishesFixtures
{
    /**
     * Copy a fixture into the app, asking before replacing an existing file
     * (unless --force).
     */
    protected function publishFile(string $from, string $to): bool
    {
        if (file_exists($to) && ! $this->option('force')
            && ! $this->confirm("[{$this->relative($to)}] already exists. Do you want to replace it?")) {
            return false;
        }

        if (! is_dir(dirname($to))) {
            mkdir(dirname($to), 0755, true);
        }

        copy($from, $to);
        $this->line("  <info>✓</info> {$this->relative($to)}");

        return true;
    }

    /**
     * Make sure the configured root view exists and renders Elm.
     *
     * An existing view that doesn't use @elm (e.g. an Inertia root `app.blade.php`)
     * is never replaced: a separate `elm.blade.php` is published instead and
     * `config/elm.php` is pointed at it.
     */
    protected function publishRootView(): void
    {
        $view = config('elm.root_view', 'app');
        $path = resource_path("views/{$view}.blade.php");

        if (is_file($path)) {
            if (str_contains(file_get_contents($path), '@elm')) {
                $this->line("  <info>✓</info> {$this->relative($path)} (already renders Elm)");

                return;
            }

            if ($view === 'app') {
                $this->writeRootView(resource_path('views/elm.blade.php'), withAppJs: false);
                $this->setRootViewConfig('elm');
                $this->line("  <comment>!</comment> {$this->relative($path)} is used by your app, so Elm pages render into resources/views/elm.blade.php");

                return;
            }

            $this->warn("  ! {$this->relative($path)} exists but does not contain @elm; add `@elm` inside its <body>.");

            return;
        }

        $this->writeRootView($path, withAppJs: is_file(resource_path('js/app.js')));
    }

    protected function writeRootView(string $path, bool $withAppJs): void
    {
        $entries = array_values(array_filter([
            is_file(resource_path('css/app.css')) ? 'resources/css/app.css' : null,
            $withAppJs ? 'resources/js/app.js' : null,
        ]));

        $vite = $entries === []
            ? ''
            : "        @vite(['" . implode("', '", $entries) . "'])\n";

        $contents = str_replace("        {{-- VITE --}}\n", $vite, file_get_contents(__DIR__ . '/../Fixtures/app.blade.php'));

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $contents);
        $this->line("  <info>✓</info> {$this->relative($path)}");
    }

    protected function setRootViewConfig(string $view): void
    {
        $config = config_path('elm.php');

        $contents = is_file($config)
            ? file_get_contents($config)
            : file_get_contents(__DIR__ . '/../../config/elm.php');

        $contents = preg_replace(
            "/'root_view'\s*=>\s*[^\n]+,/",
            "'root_view' => env('ELM_ROOT_VIEW', '{$view}'),",
            $contents
        );

        file_put_contents($config, $contents);
        config(['elm.root_view' => $view]);
        $this->line("  <info>✓</info> {$this->relative($config)} (root_view => {$view})");
    }

    protected function relative(string $path): string
    {
        return ltrim(str_replace(base_path(), '', $path), '/\\');
    }
}
