<?php

namespace LoganHenson\Elm\Commands;

use Illuminate\Console\Command;
use LoganHenson\Elm\Concerns\EnsureElmInitialized;
use LoganHenson\Elm\Concerns\PublishesFixtures;

class Install extends Command
{
    use EnsureElmInitialized, PublishesFixtures;

    protected $signature = 'elm:install {--force : Overwrite existing files without asking}';
    protected $description = 'Install Laravel Elm: npm packages, Vite plugin, Tailwind source, root view';

    /** Versions used when a package is missing (or needs a bump within its major). */
    protected const PACKAGES = [
        'laravel-elm' => '^4.1.1',
        'laravel-vite-plugin' => '^3.1',
        'vite' => '^8.0.0',
        'tailwindcss' => '^4.3.0',
        '@tailwindcss/vite' => '^4.3.0',
    ];

    /** 'v4' (installed), 'v3' (installed, left alone), 'none' (existing app without Tailwind), 'fresh' (no Vite config yet). */
    protected string $tailwind = 'v4';

    public function handle()
    {
        $this->ensureInitialized(true);
        $this->line('  <info>✓</info> resources/elm');

        $this->updateNodePackages();
        $this->installViteConfig();
        $this->installCss();
        $this->publishRootView();
        $this->ignoreGeneratedFiles();

        $this->newLine();
        $this->info('Laravel Elm installed.');
        $this->line('  1. npm install');
        $this->line('  2. php artisan elm:create Welcome   (or `php artisan elm:auth` in an app without auth)');
        $this->line('  3. composer run dev   (or `php artisan serve` + `npm run dev`)');

        return self::SUCCESS;
    }

    protected function updateNodePackages(): void
    {
        $path = base_path('package.json');

        if (! is_file($path)) {
            file_put_contents($path, json_encode(['private' => true, 'type' => 'module', 'scripts' => [
                'build' => 'vite build',
                'dev' => 'vite',
            ]], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
        }

        $package = json_decode(file_get_contents($path), true);
        $dev = $package['devDependencies'] ?? [];
        $all = $dev + ($package['dependencies'] ?? []);

        $twMajor = isset($all['tailwindcss']) ? $this->major($all['tailwindcss']) : null;
        $this->tailwind = match (true) {
            $twMajor !== null && $twMajor >= 4 => 'v4',
            $twMajor !== null => 'v3',
            $this->viteConfigPath() === null => 'fresh',
            default => 'none',
        };
        $addTailwind = in_array($this->tailwind, ['v4', 'fresh'], true);

        foreach (self::PACKAGES as $name => $version) {
            $isTailwind = in_array($name, ['tailwindcss', '@tailwindcss/vite'], true);

            if ($isTailwind && ! $addTailwind) {
                continue; // Tailwind 3 / no Tailwind: leave the app's styling alone (see readme).
            }

            if (! isset($all[$name])) {
                $dev[$name] = $version;

                continue;
            }

            // Bump within the same major only (e.g. tailwindcss ^4.0.0 -> ^4.3.0, laravel-elm ^4.0 -> ^4.1).
            if (in_array($name, ['laravel-elm', 'tailwindcss', '@tailwindcss/vite'], true)
                && $this->major($all[$name]) === $this->major($version)
                && isset($dev[$name])) {
                $dev[$name] = $version;
            }
        }

        // ("npm:..." aliases, e.g. vite-plus' vite-plus-core, are left alone.)
        if (isset($all['vite']) && ! str_starts_with($all['vite'], 'npm:') && ($this->major($all['vite']) ?? 8) < 7) {
            $this->warn('  ! laravel-elm needs Vite 7 or 8; you have vite ' . $all['vite'] . '.');
        }

        if ($this->tailwind === 'v3') {
            $this->warn('  ! Tailwind ' . $all['tailwindcss'] . ' detected: left as is. Add "./resources/elm/**/*.elm" to `content` in tailwind.config.js.');
        } elseif ($this->tailwind === 'none') {
            $this->warn('  ! No Tailwind found: not installing it. The elm:auth pages use Tailwind classes; see "Adding to an existing app" in the readme.');
        }

        ksort($dev);
        $package['devDependencies'] = $dev;

        file_put_contents($path, json_encode($package, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
        $this->line('  <info>✓</info> package.json');
    }

    protected function installViteConfig(): void
    {
        $path = $this->viteConfigPath();

        if (! $path) {
            $this->publishFile(__DIR__ . '/../Fixtures/vite.config.js', base_path('vite.config.js'));

            return;
        }

        $config = file_get_contents($path);

        if (str_contains($config, 'laravel-elm')) {
            $this->line("  <info>✓</info> {$this->relative($path)} (already uses laravel-elm)");

            return;
        }

        $patched = $this->patchViteConfig($config);

        if ($patched === null) {
            $this->warn("  ! Could not update {$this->relative($path)} automatically. Add laravel-elm by hand:");
            $this->line("      import laravelElm from 'laravel-elm';");
            $this->line("      add 'resources/js/elm.js' to laravel({ input: [...] })");
            $this->line('      add laravelElm() to plugins: [...]');

            return;
        }

        file_put_contents($path, $patched);
        $this->line("  <info>✓</info> {$this->relative($path)} (added laravel-elm)");
    }

    protected function viteConfigPath(): ?string
    {
        return collect(['vite.config.js', 'vite.config.ts', 'vite.config.mjs', 'vite.config.mts'])
            ->map(fn ($file) => base_path($file))
            ->first(fn ($file) => is_file($file));
    }

    /**
     * Add the import, the elm.js input and the plugin to an existing Vite config.
     * Returns null when the config doesn't have the expected shape.
     */
    public function patchViteConfig(string $config): ?string
    {
        $eol = str_contains($config, "\r\n") ? "\r\n" : "\n";

        // 1. import, after the last top-level import statement (which may span lines).
        if (! preg_match_all('/^import\b[^;\'"]*?([\'"])[^\'"]+\1[ \t]*;?/m', $config, $imports, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $last = end($imports[0]);
        $at = $last[1] + strlen($last[0]);
        $config = substr($config, 0, $at) . $eol . "import laravelElm from 'laravel-elm';" . substr($config, $at);

        // 2. elm.js entry in laravel({ input }).
        if (preg_match('/(\binput\s*:\s*\[)(.*?)(\s*)(\])/s', $config, $m, PREG_OFFSET_CAPTURE)) {
            $items = rtrim($m[2][0]);
            $separator = ($items === '' || str_ends_with($items, ',')) ? '' : ',';

            if (str_contains($m[2][0], "\n")) {
                // Multi-line array: new line, same indent, trailing comma (prettier style).
                $indent = preg_match('/\n([ \t]+)\S/', $m[2][0], $i) ? $i[1] : '    ';
                $insert = $items . $separator . "{$eol}{$indent}'resources/js/elm.js',";
            } else {
                $insert = $items . $separator . ($items === '' ? '' : ' ') . "'resources/js/elm.js'";
                $insert = $this->wrapLongArray($config, $m[2][1], strlen($m[2][0]), $insert, $eol);
            }
            $config = substr_replace($config, $insert, $m[2][1], strlen($m[2][0]));
        } elseif (preg_match('/\binput\s*:\s*([\'"][^\'"]+[\'"])/', $config, $m, PREG_OFFSET_CAPTURE)) {
            $config = substr_replace($config, "[{$m[1][0]}, 'resources/js/elm.js']", $m[1][1], strlen($m[1][0]));
        } else {
            return null;
        }

        // 3. plugin, first in the plugins array (`plugins: [` or vite-plus' `plugins: lazyPlugins(() => [`).
        if (! preg_match('/\bplugins\s*:\s*(?:\w+\(\s*\(\s*\)\s*=>\s*)?\[([ \t]*\r?\n([ \t]*))?/', $config, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $at = $m[0][1] + strlen($m[0][0]);
        $indent = $m[2][0] ?? '';
        $insert = isset($m[1]) ? "laravelElm(),{$eol}{$indent}" : 'laravelElm(), ';
        $config = substr($config, 0, $at) . $insert . substr($config, $at);

        // 4. vite-plus lint / fmt ignore lists: resources/js/elm.js is generated (and large), and
        //    resources/elm/elm.json is rewritten by `elm install` in Elm's own style.
        $config = preg_replace_callback('/(\bignorePatterns\s*:\s*\[)([ \t]*\r?\n([ \t]*))?/', function ($m) use ($eol) {
            return isset($m[2]) && $m[2] !== ''
                ? "{$m[1]}{$m[2]}'resources/js/elm.js',{$eol}{$m[3]}'resources/elm/**',{$eol}{$m[3]}"
                : "{$m[1]}'resources/js/elm.js', 'resources/elm/**', ";
        }, $config);

        return $config;
    }

    /**
     * Keep the line within an 80 column print width (prettier / oxfmt default):
     * a single-line array of plain strings that would get too long goes one item per line.
     */
    protected function wrapLongArray(string $config, int $offset, int $length, string $items, string $eol): string
    {
        $lineStart = strrpos(substr($config, 0, $offset), "\n");
        $lineStart = $lineStart === false ? 0 : $lineStart + 1;
        $lineEnd = strpos($config, "\n", $offset + $length);
        $lineEnd = $lineEnd === false ? strlen($config) : $lineEnd;

        $line = substr($config, $lineStart, $offset - $lineStart) . $items . substr($config, $offset + $length, $lineEnd - $offset - $length);

        if (strlen(rtrim($line)) <= 80) {
            return $items;
        }

        preg_match_all('/([\'"])(?:(?!\1).)*\1/', $items, $strings);

        // Only plain string entries can be safely reflowed.
        if (trim(preg_replace('/([\'"])(?:(?!\1).)*\1/', '', $items), " ,\t") !== '') {
            return $items;
        }

        $indent = preg_match('/^[ \t]*/', $line, $i) ? $i[0] : '';

        return $eol . implode('', array_map(fn ($s) => "{$indent}    {$s},{$eol}", $strings[0])) . $indent;
    }

    protected function installCss(): void
    {
        $path = resource_path('css/app.css');

        if (! is_file($path)) {
            if (in_array($this->tailwind, ['v4', 'fresh'], true)) {
                $this->publishFile(__DIR__ . '/../Fixtures/css/app.css', $path);
            }

            return;
        }

        $css = file_get_contents($path);

        if (str_contains($css, 'resources/elm') || str_contains($css, '../elm/')) {
            $this->line('  <info>✓</info> resources/css/app.css (already scans Elm files)');

            return;
        }

        if (! preg_match('/@import\s+[\'"]tailwindcss/', $css)) {
            $this->warn('  ! resources/css/app.css does not import Tailwind 4; see "Adding to an existing app" in the readme for styling Elm pages.');

            return;
        }

        $eol = str_contains($css, "\r\n") ? "\r\n" : "\n";
        $line = "@source '../elm/**/*.elm';";

        // Right after the last @source / @import, so it stays before @theme etc.
        if (preg_match_all('/^@(?:source|import)\b[^\n]*$/m', $css, $m, PREG_OFFSET_CAPTURE)) {
            $last = end($m[0]);
            $at = $last[1] + strlen($last[0]);
            $css = substr($css, 0, $at) . $eol . $line . substr($css, $at);
        } else {
            $css = rtrim($css) . $eol . $eol . $line . $eol;
        }

        file_put_contents($path, $css);
        $this->line('  <info>✓</info> resources/css/app.css (Tailwind now scans resources/elm)');
    }

    protected function ignoreGeneratedFiles(): void
    {
        // resources/js/elm.js is generated by the Vite plugin on every build / dev run.
        foreach (['.gitignore', '.prettierignore'] as $file) {
            $path = base_path($file);

            if (! is_file($path) || str_contains(file_get_contents($path), 'resources/js/elm.js')) {
                continue;
            }

            file_put_contents($path, rtrim(file_get_contents($path)) . PHP_EOL . '/resources/js/elm.js' . PHP_EOL);
            $this->line("  <info>✓</info> {$file} (+ /resources/js/elm.js)");
        }
    }

    protected function major(string $constraint): ?int
    {
        return preg_match('/(\d+)/', $constraint, $m) ? (int) $m[1] : null;
    }
}
