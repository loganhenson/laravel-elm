<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;

/**
 * Class Create
 * @package Tightenco\Elm\Commands
 */
class Create extends Command
{
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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dd($this->argument('program'));
    }
}
