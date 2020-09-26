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

        $page = Str::studly($this->argument('page'));

        $mainDir = resource_path('elm/pages/' . $page);
        if (! is_dir($mainDir)) {
            mkdir($mainDir, 0755, true);
        }

        file_put_contents(resource_path("elm/pages/{$page}/Main.elm"), $this->makePage($page));
    }

    private function makePage(string $page)
    {
        return str_replace('PAGE', $page, file_get_contents(__DIR__ . '/../Fixtures/Main.elm'));
    }
}
