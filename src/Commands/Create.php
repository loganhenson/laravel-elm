<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Create extends Command
{
    protected $files;
    protected $signature = 'elm:create {program}';
    protected $description = 'Create Elm program';

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $program = Str::studly($this->argument('program'));

        if (! is_dir(resource_path('elm'))) {
            $this->files->makeDirectory(resource_path('elm/'));
        }

        $this->files->makeDirectory(resource_path('elm/' . $program));

        $initialProgram = <<<EOT
module {$program} exposing (..)

import Html exposing (text)

main =
  text "Hello, World!"
EOT;

        $this->files->put(resource_path("elm/{$program}/Main.elm"), $initialProgram);
    }
}
