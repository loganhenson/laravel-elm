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

        if (! is_dir('resources/elm')) {
            $this->files->makeDirectory('resources/elm/');
        }

        $this->files->makeDirectory('resources/elm/' . $program);

        $initialProgram = <<<EOT
module {$program} exposing (..)

import Html exposing (text)

main =
  text "Hello, World!"
EOT;

        $this->files->put("resources/elm/{$program}/Main.elm", $initialProgram);
    }
}
