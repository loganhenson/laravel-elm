<?php

namespace Tightenco\Elm\Tests;

use Tightenco\Elm\Elm;

class HelperTest extends TestCase
{
    /** @test */
    function helper_function_is_facade_render()
    {
        $this->assertEquals(
            elm('Example', ['test' => 'test']),
            Elm::render('Example', ['test' => 'test'])
        );
    }
}
