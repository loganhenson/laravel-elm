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
        $program = Str::studly($this->argument('program'));

        if (! File::isDirectory($elmPath)) {
            File::makeDirectory($elmPath);
        }

        File::makeDirectory(resource_path('elm/' . $program));

        $initialProgram = <<<EOT
module {$program} exposing (..)

import Html exposing (text)

main =
  text "Hello, World!"
EOT;

        File::put(resource_path("elm/{$program}/Main.elm"), $initialProgram);
    }
}
