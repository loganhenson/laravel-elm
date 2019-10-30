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

        if (! is_dir('resources/assets/elm')) {
            $this->files->makeDirectory('resources/assets/elm/');
        }

        $this->files->makeDirectory('resources/assets/elm/' . $program);

        $initialProgram = <<<EOT
module {$program} exposing (..)

import Html exposing (div, h1, text)

main : Html.Html a
main =
   div [] [ h1 [] [text "Hello, World!"] ]
EOT;

        $this->files->put("resources/assets/elm/{$program}/Main.elm", $initialProgram);
    }
}
