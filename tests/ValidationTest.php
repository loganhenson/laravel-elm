<?php

namespace Tightenco\Elm\Tests;

use Closure;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tightenco\Elm\Elm;

class ValidationTest extends TestCase
{
    #[Test]
    public function validation_errors_are_always_shared()
    {
        $this->assertInstanceOf(Closure::class, Elm::getShared('errors'));
    }

    #[Test]
    public function validation_errors_can_be_empty()
    {
        $errors = Elm::getShared('errors')();

        $this->assertIsObject($errors);
        $this->assertEmpty(get_object_vars($errors));
    }

    #[Test]
    public function validation_errors_are_not_registered_when_already_registered()
    {
        Elm::share('errors', 'This is a validation error');

        $this->assertSame('This is a validation error', Elm::getShared('errors'));
    }

    #[Test]
    public function validation_errors_are_still_flashed_to_the_session_normally()
    {
        $this->withExceptionHandling();

        Route::post('/users/1', function (Request $request) {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
            ]);
        });

        $this->post('/users/1')
            ->assertSessionHasErrors(['name', 'email']);
    }
}
