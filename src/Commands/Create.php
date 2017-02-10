<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Artisan;

/**
 * Class Create
 * @package Tightenco\Elm\Commands
 */
class Create extends Command
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elm:create {program}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Elm program';

    /**
     * Create a new program creator command instance.
     *
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $program = studly_case($this->argument('program'));

        if (!is_dir('resources/assets/elm')) {
            $this->files->makeDirectory('resources/assets/elm/');
        }

        $this->files->makeDirectory('resources/assets/elm/' . $program);

        $initialProgram = <<<EOT
module $program exposing (..)

import Html exposing (div, h1, text)

main : Html.Html a
main =
   div [] [ h1 [] [text "Hello, World!"] ]
EOT;

        $this->files->put("resources/assets/elm/$program/Main.elm", $initialProgram);
    }
}
