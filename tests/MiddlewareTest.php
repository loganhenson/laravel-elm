<?php

namespace Tightenco\Elm\Tests;

use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateHttpResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tightenco\Elm\Response;

class MiddlewareTest extends TestCase
{
    #[Test]
    public function returns_correct_json_structure()
    {
        $this->send(
            Request::create('/users/1'),
            function () {
                return elm('Example.Test', ['user' => ['name' => 'Logan']]);
            }
        )->assertSuccessful()
            ->assertJson([
                'page' => 'Example.Test',
                'props' =>
                    [
                        'loading' => false,
                        'status' => null,
                        'viewports' =>
                            [
                            ],
                        'errors' =>
                            [
                            ],
                        'user' =>
                            [
                                'name' => 'Logan',
                            ],
                    ],
                'url' => '/users/1',
            ]);
    }

    #[Test]
    public function returns_version_if_there_is_a_vite_manifest()
    {
        // Use the existing manifest fixture
        $publicPath = __DIR__ . '/fixtures/public_with_vite_manifest';
        $this->app->instance('path.public', $publicPath);
        
        // Verify the manifest exists
        $this->assertTrue(file_exists($publicPath . '/build/manifest.json'), 'Vite manifest file should exist');

        $response = $this->send(
            Request::create('/users/1'),
            function () {
                return elm('Example.Test');
            }
        )->assertSuccessful()
            ->assertJson([
                'version' => md5_file($publicPath . '/build/manifest.json'),
                'page' => 'Example.Test',
                'props' =>
                    [
                        'loading' => false,
                        'status' => null,
                        'viewports' =>
                            [
                            ],
                        'errors' =>
                            [
                            ],
                    ],
                'url' => '/users/1',
            ]);
    }

    #[Test]
    public function laravel_elm_requests_return_json()
    {
        $request = Request::create('/users/1');
        $request->headers->add(['X-Laravel-Elm' => 'true']);

        $response = new Response('User/Edit', []);
        $response = $response->toResponse($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    #[Test]
    public function laravel_elm_requests_that_have_validation_errors_return_them_as_json_directly()
    {
        $this->withExceptionHandling();

        Route::get('/users', function (Request $request) {
            return 'test';
        });
        Route::post('/users', function (Request $request) {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
            ]);
        });

        $response = $this
            ->from('/users')
            ->followingRedirects()
            ->post('/users', [], ['X-Laravel-Elm' => 'true']);

        $response->assertHeader('X-Laravel-Elm-Errors', 'true');
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertExactJson([
            'errors' => [
                'email' => ['The email field is required.'],
                'name' => ['The name field is required.'],
            ],
        ]);
    }

    #[Test]
    public function non_laravel_elm_requests_return_normal_response_of_app_blade_with_elm()
    {
        $this->app->config->set('app.debug', true);

        View::addLocation(__DIR__ . '/../src/Fixtures');

        $request = Request::create('/users/1');

        $response = new Response('User/Edit', []);
        $response = $response->toResponse($request);

        // Check that the response contains the Elm initialization script
        $this->assertStringContainsString('window.LaravelElm', $response->getContent());
        $this->assertStringContainsString('setPage(window.location.pathname', $response->getContent());
        $this->assertInstanceOf(IlluminateHttpResponse::class, $response);
    }
}
