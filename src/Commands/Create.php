<?php

namespace LoganHenson\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LoganHenson\Elm\Concerns\EnsureElmInitialized;

class Create extends Command
{
    use EnsureElmInitialized;

    protected $signature = 'elm:create {page : e.g. Welcome or Admin.Users} {--force : Overwrite an existing page}';
    protected $description = 'Create Elm Page';

    public function handle()
    {
        $this->ensureInitialized();

        $relativePath = collect(explode('/', str_replace('.', '/', str_replace('.elm', '', $this->argument('page')))))
            ->map(fn (string $segment) => Str::studly($segment))
            ->implode('/');
        $moduleName = str_replace('/', '.', $relativePath);
        $fullFilePath = resource_path("elm/pages/{$relativePath}.elm");

        if (is_file($fullFilePath) && ! $this->option('force')
            && ! $this->confirm("[resources/elm/pages/{$relativePath}.elm] already exists. Do you want to replace it?")) {
            return self::SUCCESS;
        }

        $dir = dirname($fullFilePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullFilePath, $this->makePage($moduleName));

        $this->line("  <info>✓</info> resources/elm/pages/{$relativePath}.elm");
        $this->line("    Render it from a route with Elm::render('{$moduleName}')");

        return self::SUCCESS;
    }

    private function makePage(string $page)
    {
        return str_replace('PAGE', $page, file_get_contents(__DIR__ . '/../Fixtures/Main.elm'));
    }
}
