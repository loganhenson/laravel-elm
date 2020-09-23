<?php

namespace Tightenco\Elm\Tests;

use Illuminate\Support\Facades\Blade;

class BladeDirectiveTest extends TestCase
{
    /** @test */
    function blade_directive_is_registered()
    {
        $this->assertArrayHasKey('elm', Blade::getCustomDirectives());
    }

    /** @test */
    function elm_hot_is_the_src_if_there_is_not_a_elm_min_js_file_in_the_manifest_and_we_are_in_debug_mode()
    {
        $this->app->config->set('app.debug', true);
        $this->app->instance('path.public', __DIR__ . '/fixtures/public_with_mix_manifest_non_minified');

        $directives = Blade::getCustomDirectives();

        $this->assertEquals(
            '<script src="/js/elm-hot.js"></script>{!! $elm !!}',
            $directives['elm']()
        );
    }

    /** @test */
    function elm_min_js_is_the_src_if_there_is_a_elm_min_js_file_in_the_manifest()
    {
        $this->app->instance('path.public', __DIR__ . '/fixtures/public_with_mix_manifest_minified');

        $directives = Blade::getCustomDirectives();

        $this->assertEquals(
            '<script src="/js/elm.min.js?id=e695f3e55294533b3a87"></script>{!! $elm !!}',
            $directives['elm']()
        );
    }
}
