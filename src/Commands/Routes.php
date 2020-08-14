<?php

namespace Tightenco\Elm\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;

class Routes extends Command
{
    protected $files;
    protected $signature = 'elm:routes';
    protected $description = 'Publish routes for elm';

    public function handle()
    {
        File::put(resource_path("elm/Routes.elm"), $this->makeRoutes());
    }

    private function normalizeRouteName(string $name)
    {
        return lcfirst(str_replace(' ', '', ucwords(preg_replace("/[^A-Za-z0-9 ]/", ' ', $name))));
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
            return $paramList . "{$param} ";
        }, ' ');
    }

    private function makeRoutes()
    {
        $routes = collect(app('router')->getRoutes()->getRoutesByName())->map(function (Route $route) {
            return collect($route)->only(['uri', 'methods'])
                ->when(method_exists($route, 'bindingFields'), function ($collection) use ($route) {
                    return $collection->put('params', $route->parameterNames());
                });
        })->toArray();

        ob_start(); ?>
<?php foreach ($routes as $name => $route): ?>
<?= $this->normalizeRouteName($name) ?> :<?= $this->generateTypeDefFromParams($route['params']) ?>String
<?= $this->normalizeRouteName($name) ?><?= $this->generateParamNamesFromParams($route['params']) ?>=
    "/<?= $route['uri'] ?>"
<?php foreach ($route['params'] as $param): ?>
        |> String.replace "{<?= $param ?>}" <?= $param ?>
<?php endforeach ?>


<?php endforeach ?>
        <?php $elmRoutes = ob_get_clean();

        return str_replace('ROUTES', $elmRoutes, File::get(__DIR__ . '/../Fixtures/Routes.elm'));
    }
}