<?php

namespace Tightenco\Elm\Tests;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Testing\TestResponse;
use Orchestra\Testbench\TestCase as Orchestra;
use PHPUnit\Framework\Attributes\Test;
use Tightenco\Elm\Elm;
use Tightenco\Elm\ElmServiceProvider;
use Tightenco\Elm\Middleware;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
        
        // Mock Vite for tests
        $this->withoutVite();
    }

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Kernel::class)->pushMiddleware(StartSession::class);
    }

    public function send(Request $request, callable $handler)
    {
        $request->headers->add(['X-Laravel-Elm' => 'true']);

        $response = (new Middleware)->handle($request, function ($request) use ($handler) {
            return $handler($request)->toResponse($request);
        });

        return TestResponse::fromBaseResponse($response);
    }

    protected function getPackageProviders($app)
    {
        return [ElmServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Elm' => Elm::class,
        ];
    }
}
