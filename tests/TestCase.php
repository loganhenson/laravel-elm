<?php

namespace LoganHenson\Elm\Tests;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Testing\TestResponse;
use Orchestra\Testbench\TestCase as Orchestra;
use PHPUnit\Framework\Attributes\Test;
use LoganHenson\Elm\Elm;
use LoganHenson\Elm\ElmServiceProvider;
use LoganHenson\Elm\Middleware;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Vite for tests
        $this->withoutVite();
    }

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));

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
