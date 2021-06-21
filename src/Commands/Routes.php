<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Tightenco\Elm\Concerns\EnsureElmInitialized;

class Routes extends Command
{
    use EnsureElmInitialized;

    protected $files;
    protected $signature = 'elm:routes';
    protected $description = 'Publish routes for elm';

    public function handle()
    {
        $this->ensureInitialized();

        file_put_contents(resource_path("elm/laravel-elm-stuff/Routes.elm"), $this->makeRoutes(
            $this->getRoutes()
        ));
    }

    private function normalizeRouteName(string $name)
    {
        return lcfirst(str_replace(' ', '', ucwords(preg_replace("/[^A-Za-z0-9 ]/", ' ', $name))));
    }

    private function normalizeRouteUri(string $uri)
    {
        return str_replace('?', '', $uri);
    }

    private function generateTypeDefFromParams(array $params)
    {
        return array_reduce($params, function ($def, $param) {
            return $def . "String -> ";
        }, ' ');
    }

    private function generateParamNamesFromParams(array $params)
    {
        return array_reduce($params, function ($paramList, $param) {
            $normalizedParam = str_replace('?', '', $param);
            return $paramList . "{$normalizedParam} ";
        }, ' ');
    }

    private function getRoutes()
    {
        return collect(app('router')->getRoutes()->getRoutesByName())->map(function (Route $route) {
            return collect($route)->only(['uri', 'methods'])
                ->when(method_exists($route, 'bindingFields'), function ($collection) use ($route) {
                    return $collection->put('params', $route->parameterNames());
                });
        })->toArray();
    }

    public function makeRoutes(array $routes)
    {
        $elmRoutes = '';

        foreach ($routes as $name => $route) {
            $typeSignature = $this->normalizeRouteName($name)
                . ' :'
                . $this->generateTypeDefFromParams($route['params'])
                . 'String';

            $functionAndArgs = $this->normalizeRouteName($name)
                . $this->generateParamNamesFromParams($route['params'])
                . '=';

            $functionBody = "    \"" . $this->normalizeRouteUri($route['uri']) . "\"";

            foreach ($route['params'] as $param) {
                $functionBody .= "\n        " . "|> String.replace \"{{$param}}\" {$param}";
            }

            $elmRoutes .= <<<function


                {$typeSignature}
                {$functionAndArgs}
                {$functionBody}

                function;
        }

        return str_replace('ROUTES', rtrim($elmRoutes, "\n"), file_get_contents(__DIR__ . '/../Fixtures/Routes.elm'));
    }
}
