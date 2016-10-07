<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class Create
 * @package Tightenco\Elm\Commands
 */
class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elm:install {program} {package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Elm package into Elm program';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $process = (new Process(
            'elm-package install --yes ' . $this->argument('package'),
            'resources/assets/elm/' . $this->argument('program')
        ))->setTimeout(null);

        return $process->mustRun();
    }
}
