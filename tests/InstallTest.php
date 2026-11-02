<?php

namespace LoganHenson\Elm\Tests;

use Illuminate\Filesystem\Filesystem;
use LoganHenson\Elm\Commands\Install;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class InstallTest extends TestCase
{
    private string $sandbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = sys_get_temp_dir() . '/laravel-elm-install-' . uniqid();
        (new Filesystem)->makeDirectory($this->sandbox . '/resources/views', 0755, true);
        (new Filesystem)->makeDirectory($this->sandbox . '/resources/css', 0755, true);
        (new Filesystem)->makeDirectory($this->sandbox . '/resources/js', 0755, true);
        (new Filesystem)->makeDirectory($this->sandbox . '/config', 0755, true);
        $this->app->setBasePath($this->sandbox);
        $this->app->useConfigPath($this->sandbox . '/config');
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->sandbox);

        parent::tearDown();
    }

    public static function viteConfigs(): array
    {
        return [
            'laravel 13 skeleton' => ['skeleton.js'],
            'react starter kit (vite-plus, lazyPlugins)' => ['react-kit.ts'],
            'livewire starter kit (comments in input)' => ['livewire-kit.js'],
            'string input + multi-line import' => ['string-input.js'],
        ];
    }

    #[Test]
    #[DataProvider('viteConfigs')]
    public function it_patches_existing_vite_configs(string $fixture)
    {
        $original = file_get_contents(__DIR__ . "/fixtures/vite/{$fixture}");
        $patched = (new Install)->patchViteConfig($original);

        $this->assertNotNull($patched);
        $this->assertSame(1, substr_count($patched, "import laravelElm from 'laravel-elm';"));
        $this->assertSame(1, substr_count($patched, 'laravelElm()'));
        $this->assertMatchesRegularExpression("/input\s*:\s*\[[^\]]*'resources\/js\/elm\.js'/s", $patched);

        // Nothing from the original config is lost (fonts, other plugins, lint config...).
        foreach (preg_split('/\R/', $original) as $line) {
            if (preg_match('/\b(input|plugins)\s*:/', $line)) {
                continue; // the lines we edit
            }
            $this->assertStringContainsString(trim($line), $patched);
        }

        // The import lands after the last import statement, before the config.
        $this->assertLessThan(strpos($patched, 'export default'), strpos($patched, "import laravelElm from 'laravel-elm';"));
        $this->assertGreaterThan(strrpos($original, "from '") ?: 0, strpos($patched, "import laravelElm from 'laravel-elm';"));
    }

    #[Test]
    public function it_adds_elm_js_to_vite_plus_ignore_patterns()
    {
        $patched = (new Install)->patchViteConfig(file_get_contents(__DIR__ . '/fixtures/vite/react-kit.ts'));

        // lint + fmt
        $this->assertSame(2, preg_match_all("/ignorePatterns:\s*\[\s*'resources\/js\/elm\.js',/", $patched));
    }

    #[Test]
    public function it_returns_null_for_configs_it_does_not_understand()
    {
        $this->assertNull((new Install)->patchViteConfig("export default { plugins: getPlugins() }\n"));
    }

    #[Test]
    public function fresh_skeleton_install_is_non_destructive()
    {
        copy(__DIR__ . '/fixtures/vite/skeleton.js', $this->sandbox . '/vite.config.js');
        file_put_contents($this->sandbox . '/resources/css/app.css', "@import 'tailwindcss';\n\n@source '../../storage/framework/views/*.php';\n\n@theme {\n    --font-sans: 'Instrument Sans';\n}\n");
        file_put_contents($this->sandbox . '/resources/js/app.js', '');
        file_put_contents($this->sandbox . '/resources/views/welcome.blade.php', '@fonts');
        file_put_contents($this->sandbox . '/.gitignore', "/vendor\n/node_modules\n");
        file_put_contents($this->sandbox . '/package.json', json_encode(['devDependencies' => [
            '@tailwindcss/vite' => '^4.0.0', 'laravel-vite-plugin' => '^3.1', 'tailwindcss' => '^4.0.0', 'vite' => '^8.0.0',
        ]]));

        $this->artisan('elm:install')->assertSuccessful();

        $vite = file_get_contents($this->sandbox . '/vite.config.js');
        $this->assertStringContainsString("bunny('Instrument Sans'", $vite);
        $this->assertStringContainsString('laravelElm()', $vite);

        $css = file_get_contents($this->sandbox . '/resources/css/app.css');
        $this->assertStringContainsString("@source '../elm/**/*.elm';", $css);
        $this->assertStringContainsString("--font-sans: 'Instrument Sans';", $css);
        $this->assertLessThan(strpos($css, '@theme'), strpos($css, "@source '../elm/**/*.elm';"));

        $view = file_get_contents($this->sandbox . '/resources/views/app.blade.php');
        $this->assertStringContainsString("@vite(['resources/css/app.css', 'resources/js/app.js'])", $view);
        $this->assertStringContainsString('@elm', $view);
        $this->assertStringNotContainsString('VITE', $view);

        $package = json_decode(file_get_contents($this->sandbox . '/package.json'), true)['devDependencies'];
        $this->assertSame('^4.1.1', $package['laravel-elm']);
        $this->assertSame('^4.3.0', $package['tailwindcss']);
        $this->assertSame('^4.3.0', $package['@tailwindcss/vite']);
        $this->assertSame('^8.0.0', $package['vite']);

        $this->assertStringContainsString('/resources/js/elm.js', file_get_contents($this->sandbox . '/.gitignore'));
        $this->assertFileDoesNotExist($this->sandbox . '/config/elm.php');

        // Idempotent.
        $this->artisan('elm:install')->assertSuccessful();
        $this->assertSame($vite, file_get_contents($this->sandbox . '/vite.config.js'));
        $this->assertSame($css, file_get_contents($this->sandbox . '/resources/css/app.css'));
    }

    #[Test]
    public function inertia_apps_keep_their_root_view_and_get_an_elm_view()
    {
        copy(__DIR__ . '/fixtures/vite/react-kit.ts', $this->sandbox . '/vite.config.ts');
        file_put_contents($this->sandbox . '/resources/css/app.css', "@import 'tailwindcss';\n");
        $inertiaRoot = "<html><body>@inertia @vite(['resources/js/app.tsx'])</body></html>";
        file_put_contents($this->sandbox . '/resources/views/app.blade.php', $inertiaRoot);
        file_put_contents($this->sandbox . '/package.json', json_encode([
            'dependencies' => ['tailwindcss' => '^4.0.0', 'vite' => 'npm:@voidzero-dev/vite-plus-core@1.4.0', 'laravel-vite-plugin' => '^3.0.0'],
        ]));

        $this->artisan('elm:install', ['--force' => true])
            ->doesntExpectOutputToContain('needs Vite 7 or 8')
            ->assertSuccessful();

        $this->assertSame($inertiaRoot, file_get_contents($this->sandbox . '/resources/views/app.blade.php'));

        $elmView = file_get_contents($this->sandbox . '/resources/views/elm.blade.php');
        $this->assertStringContainsString('@elm', $elmView);
        $this->assertStringContainsString("@vite(['resources/css/app.css'])", $elmView);
        $this->assertStringNotContainsString('app.tsx', $elmView);

        $this->assertStringContainsString("'root_view' => env('ELM_ROOT_VIEW', 'elm')", file_get_contents($this->sandbox . '/config/elm.php'));
        $this->assertSame('elm', config('elm.root_view'));

        $package = json_decode(file_get_contents($this->sandbox . '/package.json'), true);
        $this->assertSame('npm:@voidzero-dev/vite-plus-core@1.4.0', $package['dependencies']['vite']);
        $this->assertArrayNotHasKey('vite', $package['devDependencies']);
        $this->assertArrayNotHasKey('tailwindcss', $package['devDependencies']);
    }

    #[Test]
    public function apps_without_tailwind_are_not_given_tailwind()
    {
        file_put_contents($this->sandbox . '/vite.config.js', file_get_contents(__DIR__ . '/fixtures/vite/string-input.js'));
        file_put_contents($this->sandbox . '/resources/css/app.css', "@import 'bootstrap/dist/css/bootstrap.css';\n");
        file_put_contents($this->sandbox . '/package.json', json_encode(['devDependencies' => ['bootstrap' => '^5.3.0', 'vite' => '^7.0.0']]));

        $this->artisan('elm:install')
            ->expectsOutputToContain('No Tailwind found')
            ->assertSuccessful();

        $package = json_decode(file_get_contents($this->sandbox . '/package.json'), true)['devDependencies'];
        $this->assertArrayNotHasKey('tailwindcss', $package);
        $this->assertSame('^7.0.0', $package['vite']);
        $this->assertSame("@import 'bootstrap/dist/css/bootstrap.css';\n", file_get_contents($this->sandbox . '/resources/css/app.css'));
    }
}
