<?php

namespace Tightenco\Elm\Tests;

use Illuminate\Foundation\Auth\User;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\Request;

class ResponseTest extends TestCase
{
    #[Test]
    public function can_use_arrayable_as_props()
    {
        $this->send(
            Request::create('/users/1'),
            function () {
                $user = new User;
                $user->name = 'Logan';

                return elm('Example.Test', ['user' => $user]);
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
}
