<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Create extends Command
{
    protected $files;
    protected $signature = 'elm:create {page}';
    protected $description = 'Create Elm Page';

    public function handle()
    {
        $elmPath = resource_path('elm');

        $this->ensureInitialized($elmPath);

        $page = Str::studly($this->argument('page'));

        File::makeDirectory(resource_path('elm/' . $page));

        File::put(resource_path("elm/{$page}/Main.elm"), $this->makePage($page));
    }

    private function makePage(string $page)
    {
        return str_replace('PAGE', $page, File::get(__DIR__ . '/../Fixtures/Main.elm'));
    }

    private function ensureInitialized($elmPath)
    {
        if (! File::isDirectory($elmPath)) {
            File::makeDirectory($elmPath);
        }

        $elmJsonPath = $elmPath . '/elm.json';

        if (! File::isFile($elmJsonPath)) {
            File::copy(__DIR__ . '/../Fixtures/elm.json', $elmJsonPath);
        }
    }
}
