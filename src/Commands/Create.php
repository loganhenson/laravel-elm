<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Create extends Command
{
    protected $files;
    protected $signature = 'elm:create {program}';
    protected $description = 'Create Elm program';

    public function handle()
    {
        $elmPath = resource_path('elm');

        $this->ensureInitialized($elmPath);

        $program = Str::studly($this->argument('program'));

        File::makeDirectory(resource_path('elm/' . $program));

        $initialProgram = <<<EOT
module {$program}.Main exposing (..)

import Html exposing (text)

main =
  text "Hello, World!"
EOT;

        File::put(resource_path("elm/{$program}/Main.elm"), $initialProgram);
    }

    private function ensureInitialized($elmPath)
    {
        if (! File::isDirectory($elmPath)) {
            File::makeDirectory($elmPath);
        }

        $elmJsonPath = $elmPath . '/elm.json';

        if (! File::isFile($elmJsonPath)) {
            File::copy($elmJsonPath, __DIR__ . '/../Fixtures/elm.json');
        }
    }
}
