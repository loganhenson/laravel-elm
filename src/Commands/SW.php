<?php

namespace LoganHenson\Elm\Commands;

use Illuminate\Console\Command;

class SW extends Command
{
    protected $signature = 'elm:sw';
    protected $description = 'Publish service worker';

    public function handle()
    {
        $this->line('Service Worker Checklist:');

        $manifestPath = public_path('manifest.json');
        if (! is_file($manifestPath)) {
            $manifest = json_decode(file_get_contents(__DIR__ . '/../Fixtures/manifest.json'), true);
            $manifest['name'] = config('app.name');
            $manifest['start_url'] = config('app.url');
            file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
            $this->line('✓ manifest.json - newly generated');
        } else {
            $this->line("✓ {$this->shortPath($manifestPath)}");
        }

        $viteManifestPath = public_path('build/manifest.json');
        if (! is_file($viteManifestPath)) {
            $this->line('✗ build/manifest.json - Try running `npm run build` first');

            return self::FAILURE;
        }

        $this->line("✓ {$this->shortPath($viteManifestPath)}");

        $files = collect(json_decode(file_get_contents($viteManifestPath), true))
            ->flatMap(fn (array $chunk) => array_merge(
                [$chunk['file']],
                $chunk['css'] ?? [],
                $chunk['assets'] ?? [],
            ))
            ->unique()
            ->map(fn (string $file) => '/build/' . ltrim($file, '/'))
            ->values();

        // Vite assets are content-hashed, so no revision is needed.
        $entries = $files->map(fn (string $file) => "{url: '{$file}', revision: null}")->join(',');

        $file = <<<file
        importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.3.0/workbox-sw.js');

        workbox.precaching.precacheAndRoute([{$entries}]);

        file;

        $swPath = public_path('sw.js');
        file_put_contents($swPath, $file);

        $this->line("🔧 worker generated: {$this->shortPath($swPath)}");

        $files->each(fn (string $file) => $this->line("    💾 {$file}"));

        return self::SUCCESS;
    }

    private function shortPath(string $path)
    {
        return str_replace(base_path(), '', $path);
    }
}
