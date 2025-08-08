<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Tightenco\Elm\Concerns\EnsureElmInitialized;

class Auth extends Command
{
    use EnsureElmInitialized;

    protected $signature = 'elm:auth {--force : Overwrite existing files}';
    protected $description = 'Scaffold login and registration views and routes';

    public function handle()
    {
        $this->ensureInitialized();
        $this->exportPages();
        $this->exportSrc();
        $this->exportHomeControllerAndView();
        $this->exportAuthControllers();
        $this->exportAppBladeTemplate();
        $this->exportRoutes();

        $this->info('Authentication scaffolding generated successfully.');
    }

    protected function exportPages()
    {
        $authDirPath = resource_path('elm/pages/Auth');

        if (is_dir($authDirPath) && ! $this->option('force')) {
            if (! $this->confirm("[{$authDirPath}] already exists. Do you want to replace it?")) {
                return;
            }
        }

        if (! is_dir($authDirPath)) {
            mkdir($authDirPath, 0755, true);
        }

        $process = Process::fromShellCommandline(
            'cp -R ' . realpath(__DIR__ . '/../Fixtures/Auth/elm/pages/Auth') . "/* {$authDirPath}",
        );

        $process->run();
    }

    protected function exportSrc()
    {
        $srcAuthDirPath = resource_path('elm/src/Auth');

        if (is_dir($srcAuthDirPath) && ! $this->option('force')) {
            if (! $this->confirm("[{$srcAuthDirPath}] already exists. Do you want to replace it?")) {
                return;
            }
        }

        if (! is_dir($srcAuthDirPath)) {
            mkdir($srcAuthDirPath, 0755, true);
        }

        $process = Process::fromShellCommandline(
            'cp -R ' . realpath(__DIR__ . '/../Fixtures/Auth/elm/src/Auth') . "/* {$srcAuthDirPath}",
        );

        $process->run();
    }

    protected function exportHomeControllerAndView()
    {
        $controller = app_path('Http/Controllers/HomeController.php');

        $putHomeControllerAndView = function () use ($controller) {
            file_put_contents($controller, file_get_contents(__DIR__ . '/../Fixtures/Auth/Controllers/HomeController.php'));
            $this->call('elm:create', ['page' => 'Home']);
        };

        if (file_exists($controller) && ! $this->option('force')) {
            if ($this->confirm("The [HomeController.php] file already exists. Do you want to replace it?")) {
                $putHomeControllerAndView();
            }
        } else {
            $putHomeControllerAndView();
        }
    }

    protected function exportAuthControllers()
    {
        $directory = app_path('Http/Controllers/Auth');

        if (is_dir($directory) && ! $this->option('force')) {
            if (! $this->confirm("[{$directory}] already exists. Do you want to replace it?")) {
                return;
            }
        }

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $process = Process::fromShellCommandline(
            'cp -R ' . realpath(__DIR__ . '/../Fixtures/Auth/Controllers/Auth') . "/* {$directory}",
        );

        $process->run();
    }

    protected function exportAppBladeTemplate()
    {
        $appBladeTemplate = resource_path('views/app.blade.php');

        $putAppBladeTemplate = function () use ($appBladeTemplate) {
            file_put_contents($appBladeTemplate, file_get_contents(__DIR__ . '/../Fixtures/app.blade.php'));
        };

        if (file_exists($appBladeTemplate) && ! $this->option('force')) {
            if ($this->confirm("The [app.blade.php] file already exists. Do you want to replace it?")) {
                $putAppBladeTemplate();
            }
        } else {
            $putAppBladeTemplate();
        }
    }

    protected function exportRoutes()
    {
        file_put_contents(
            base_path('routes/web.php'),
            str_replace("<?php\n", '', file_get_contents(__DIR__ . '/../Fixtures/Auth/routes/web.php')),
            FILE_APPEND
        );

        // Call from separate process to ensure the new routes are loaded.
        // (i.e. we can't do `$this->call('elm:routes')`)
        $process = Process::fromShellCommandline(
            'php artisan elm:routes',
        );

        $process->run();
    }
}
