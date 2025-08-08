<?php

namespace Tightenco\Elm\Tests;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;

class BladeDirectiveTest extends TestCase
{
    #[Test]
    public function blade_directive_is_registered()
    {
        $this->assertArrayHasKey('elm', Blade::getCustomDirectives());
    }

    #[Test]
    public function blade_directive_uses_vite()
    {
        $directives = Blade::getCustomDirectives();
        $output = $directives['elm']('');
        
        // Check that it uses Vite
        $this->assertStringContainsString('Illuminate\Foundation\Vite', $output);
        $this->assertStringContainsString('resources/js/elm.js', $output);
        $this->assertStringContainsString('{!! $elm !!}', $output);
    }

    #[Test]
    public function blade_directive_accepts_custom_path()
    {
        $directives = Blade::getCustomDirectives();
        $output = $directives['elm']('"resources/js/custom-elm.js"');
        
        // Check that it uses the custom path
        $this->assertStringContainsString('resources/js/custom-elm.js', $output);
    }
}
