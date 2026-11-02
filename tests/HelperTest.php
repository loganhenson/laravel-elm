<?php

namespace LoganHenson\Elm\Tests;

use Illuminate\Http\Request;
use LoganHenson\Elm\Elm;
use LoganHenson\Elm\Response;
use PHPUnit\Framework\Attributes\Test;

class HelperTest extends TestCase
{
    #[Test]
    public function helper_function_is_facade_render()
    {
        $request = Request::create('/');
        $request->headers->add(['X-Laravel-Elm' => 'true']);

        $helper = elm('Example', ['test' => 'test']);
        $facade = Elm::render('Example', ['test' => 'test']);

        $this->assertInstanceOf(Response::class, $helper);
        $this->assertEquals(
            $facade->toResponse($request)->getData(true),
            $helper->toResponse($request)->getData(true),
        );
    }
}
