<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tightenco\Elm\Concerns\EnsureElmInitialized;

class Create extends Command
{
    protected $files;
    protected $signature = 'elm:create {page}';
    protected $description = 'Create Elm Page';

    use EnsureElmInitialized;

    public function handle()
    {
        $this->ensureInitialized();

        $page = Str::studly($this->argument('page'));

        File::makeDirectory(resource_path('elm/' . $page));

        File::put(resource_path("elm/{$page}/Main.elm"), $this->makePage($page));
    }

    private function makePage(string $page)
    {
        return str_replace('PAGE', $page, File::get(__DIR__ . '/../Fixtures/Main.elm'));
    }
}
