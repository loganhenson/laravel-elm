<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Tightenco\Elm\Concerns\EnsureElmInitialized;

class Create extends Command
{
    use EnsureElmInitialized;

    protected $files;
    protected $signature = 'elm:create {page}';
    protected $description = 'Create Elm Page';

    public function handle()
    {
        $this->ensureInitialized();

        $relativePath = Str::studly(str_replace('.', '/', str_replace('.elm', '', $this->argument('page'))));
        $moduleName = str_replace('/', '.', $relativePath);
        $fullFilePath = resource_path("elm/pages/{$relativePath}.elm");

        $dir = dirname($fullFilePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullFilePath, $this->makePage($moduleName));
    }

    private function makePage(string $page)
    {
        return str_replace('PAGE', $page, file_get_contents(__DIR__ . '/../Fixtures/Main.elm'));
    }
}
