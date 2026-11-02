<?php

namespace LoganHenson\Elm\Tests;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandsTest extends TestCase
{
    private string $sandbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = sys_get_temp_dir() . '/laravel-elm-' . uniqid();
        (new Filesystem)->makeDirectory($this->sandbox . '/public/build', 0755, true);
        (new Filesystem)->makeDirectory($this->sandbox . '/resources/views', 0755, true);
        $this->app->setBasePath($this->sandbox);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->sandbox);

        parent::tearDown();
    }

    #[Test]
    public function install_scaffolds_vite_tailwind_and_elm()
    {
        file_put_contents($this->sandbox . '/package.json', json_encode(['devDependencies' => ['vite' => '^7.0.4', 'axios' => '^1.11.0']]));

        $this->artisan('elm:install', ['--force' => true])->assertSuccessful();

        $package = json_decode(file_get_contents($this->sandbox . '/package.json'), true);
        $this->assertArrayHasKey('laravel-elm', $package['devDependencies']);
        $this->assertSame('^1.11.0', $package['devDependencies']['axios']);
        $this->assertSame('^4.3.0', $package['devDependencies']['tailwindcss']);
        $this->assertSame('^4.3.0', $package['devDependencies']['@tailwindcss/vite']);
        $this->assertSame('^8.0.0', $package['devDependencies']['vite']);
        $this->assertSame('^3.1', $package['devDependencies']['laravel-vite-plugin']);

        $this->assertStringContainsString('laravelElm(', file_get_contents($this->sandbox . '/vite.config.js'));
        $this->assertStringContainsString("@import 'tailwindcss'", file_get_contents($this->sandbox . '/resources/css/app.css'));
        $this->assertStringContainsString('@elm', file_get_contents($this->sandbox . '/resources/views/app.blade.php'));
        $this->assertFileExists($this->sandbox . '/resources/elm/elm.json');
        $this->assertFileExists($this->sandbox . '/resources/elm/laravel-elm-stuff/LaravelElm.elm');
    }

    #[Test]
    public function create_makes_an_elm_page()
    {
        $this->artisan('elm:create', ['page' => 'Users.Show'])->assertSuccessful();

        $page = file_get_contents($this->sandbox . '/resources/elm/pages/Users/Show.elm');
        $this->assertStringStartsWith('module Users.Show exposing', $page);
    }

    #[Test]
    public function sw_precaches_assets_from_the_vite_manifest()
    {
        file_put_contents($this->sandbox . '/public/build/manifest.json', json_encode([
            'resources/js/app.js' => ['file' => 'assets/app-abc.js', 'css' => ['assets/app-def.css'], 'isEntry' => true],
            'resources/js/elm.js' => ['file' => 'assets/elm-123.js', 'isEntry' => true],
        ]));

        $this->artisan('elm:sw')->assertSuccessful();

        $sw = file_get_contents($this->sandbox . '/public/sw.js');
        $this->assertStringContainsString("{url: '/build/assets/app-abc.js', revision: null}", $sw);
        $this->assertStringContainsString("{url: '/build/assets/app-def.css', revision: null}", $sw);
        $this->assertStringContainsString("{url: '/build/assets/elm-123.js', revision: null}", $sw);
        $this->assertFileExists($this->sandbox . '/public/manifest.json');
    }

    #[Test]
    public function sw_fails_without_a_vite_build()
    {
        $this->artisan('elm:sw')->assertFailed();
    }
}
