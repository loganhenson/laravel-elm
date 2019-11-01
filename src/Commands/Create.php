<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Create extends Command
{
    protected $files;
    protected $signature = 'elm:create {program} {--with-flags}';
    protected $description = 'Create Elm program';

    public function handle()
    {
        $elmPath = resource_path('elm');

        $this->ensureInitialized($elmPath);

        $program = Str::studly($this->argument('program'));

        File::makeDirectory(resource_path('elm/' . $program));

        $initialProgram = $this->option('with-flags')
            ? $this->makeProgramWithFlags($program)
            : $this->makeBasicProgram($program);

        File::put(resource_path("elm/{$program}/Main.elm"), $initialProgram);
    }

    private function makeBasicProgram(string $program)
    {
        return str_replace('PROGRAM', $program, File::get(__DIR__ . '/../Fixtures/BasicProgram.php'));
    }

    private function makeProgramWithFlags(string $program)
    {
        return str_replace('PROGRAM', $program, File::get(__DIR__ . '/../Fixtures/ProgramWithFlags.elm'));
    }

    private function ensureInitialized($elmPath)
    {
        if (! File::isDirectory($elmPath)) {
            File::makeDirectory($elmPath);
        }

        $elmJsonPath = $elmPath . '/elm.json';

        if (! File::isFile($elmJsonPath)) {
            File::copy(__DIR__ . '/../Fixtures/elm.json', $elmJsonPath);
        }
    }
}
