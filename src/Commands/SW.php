<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SW extends Command
{
    protected $files;
    protected $signature = 'elm:pwa';
    protected $description = 'Publish service worker';

    public function handle()
    {
        $this->line("Service Worker Checklist:");

        $manifestPath = public_path('manifest.json');
        if (! File::exists($manifestPath)) {
            File::put($manifestPath, File::get(__DIR__ . '/../Fixtures/manifest.json'));
            $this->line('✓ manifest.json - newly generated');
        } else {
            $this->line("✓ {$this->shortPath($manifestPath)}");
        }

        $mixManifestPath = public_path('mix-manifest.json');
        if (! File::exists($mixManifestPath)) {
            $this->line('✗ mix-manifest.json - Try running `npm run (dev/watch/prod)` first');
        } else {
            $this->line("✓ {$this->shortPath($mixManifestPath)}");

            $files = collect(json_decode(File::get($mixManifestPath), true))->values();

            $entries = $files->map(function (string $file) {
                return "{url: '{$file}', revision: null}";
            })->join(',');

            $file = <<<file
            importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.1.2/workbox-sw.js');

            workbox.precaching.precacheAndRoute([{$entries}]);
            file;

            $swPath = public_path('sw.js');
            File::put($swPath, $file);

            $this->line("🔧 worker generated: {$this->shortPath($swPath)}");

            $files->each(function (string $file) {
                $this->line("    💾 {$file}");
            });
        }
    }

    private function shortPath(string $path)
    {
        return str_replace(getcwd(), '', $path);
    }
}
