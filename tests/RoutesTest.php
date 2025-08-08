<?php

namespace Tightenco\Elm\Tests;

use PHPUnit\Framework\Attributes\Test;
use Tightenco\Elm\Commands\Routes;

class RoutesTest extends TestCase
{
    #[Test]
    public function base_route_of_single_slash_is_the_same_in_elm_routes_file()
    {
        $routes = [
            "welcome" => [
                "uri" => "/",
                "methods" => [
                    "GET",
                    "HEAD",
                ],
                "params" => [],
            ],
        ];

        $expectedRoute = <<<route
            welcome : String
            welcome =
                "/"
            route;

        $this->assertStringContainsString(
            $expectedRoute,
            (new Routes())->makeRoutes($routes)
        );
    }

    #[Test]
    public function starting_a_route_with_a_slash_includes_a_slash_in_elm()
    {
        $routes = [
            "test" => [
                "uri" => "/test",
                "methods" => [
                    "GET",
                    "HEAD",
                ],
                "params" => [],
            ],
        ];

        $expectedRoute = <<<route
            test : String
            test =
                "/test"
            route;

        $this->assertStringContainsString(
            $expectedRoute,
            (new Routes())->makeRoutes($routes)
        );
    }

    #[Test]
    public function not_starting_a_route_with_a_slash_also_follows_that_in_elm()
    {
        $routes = [
            "test" => [
                "uri" => "test",
                "methods" => [
                    "GET",
                    "HEAD",
                ],
                "params" => [],
            ],
        ];

        $expectedRoute = <<<route
            test : String
            test =
                "test"
            route;

        $this->assertStringContainsString(
            $expectedRoute,
            (new Routes())->makeRoutes($routes)
        );
    }
}
