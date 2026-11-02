<?php

namespace LoganHenson\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use LoganHenson\Elm\Concerns\EnsureElmInitialized;
use LoganHenson\Elm\Concerns\PublishesFixtures;
use Symfony\Component\Process\Process;

class Auth extends Command
{
    use EnsureElmInitialized, PublishesFixtures;

    protected $signature = 'elm:auth {--force : Overwrite existing files}';
    protected $description = 'Scaffold login and registration views and routes';

    public function handle()
    {
        $this->ensureInitialized();

        $fixtures = __DIR__ . '/../Fixtures/Auth';

        $this->publishDirectory("{$fixtures}/elm/pages/Auth", resource_path('elm/pages/Auth'));
        $this->publishDirectory("{$fixtures}/elm/src/Auth", resource_path('elm/src/Auth'));
        $this->publishFile("{$fixtures}/elm/pages/Home.elm", resource_path('elm/pages/Home.elm'));
        $this->publishFile("{$fixtures}/Controllers/HomeController.php", app_path('Http/Controllers/HomeController.php'));
        $this->publishDirectory("{$fixtures}/Controllers/Auth", app_path('Http/Controllers/Auth'));
        $this->publishRootView();
        $this->exportRoutes();

        $this->info('Authentication scaffolding generated successfully.');
        $this->line('Run `php artisan migrate` if you have not yet, then visit /register.');
    }

    protected function publishDirectory(string $from, string $to)
    {
        if (is_dir($to) && ! $this->option('force')
            && ! $this->confirm("[{$this->relative($to)}] already exists. Do you want to replace it?")) {
            return;
        }

        (new Filesystem)->copyDirectory($from, $to);
        $this->line("  <info>✓</info> {$this->relative($to)}");
    }

    protected function exportRoutes()
    {
        $web = base_path('routes/web.php');
        $contents = is_file($web) ? file_get_contents($web) : "<?php\n";

        if (! str_contains($contents, 'Elm::authRoutes()')) {
            $routes = preg_replace('/^<\?php\s*/', '', file_get_contents(__DIR__ . '/../Fixtures/Auth/routes/web.php'));
            file_put_contents($web, rtrim($contents) . "\n\n" . $routes);
        }

        // Run in a separate process so the routes we just added are registered
        // (i.e. we can't do `$this->call('elm:routes')`).
        (new Process([PHP_BINARY, 'artisan', 'elm:routes'], base_path()))->run();
    }
}
