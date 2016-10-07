<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
     * @return mixed
     */
    public function handle()
    {
        $this->files->makeDirectory('resources/assets/elm/' . $this->argument('program'));

        $initialProgram = <<<EOT
import Html exposing (div, h1, text)

main =
   div [] [ h1 [] [text "Hello, World!"] ]
EOT;

        return $this->files->put('resources/assets/elm/' . $this->argument('program') . '/Main.elm', $initialProgram);
    }
}
