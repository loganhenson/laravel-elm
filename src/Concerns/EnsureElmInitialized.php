<?php

namespace Tightenco\Elm\Concerns;

trait EnsureElmInitialized
{
    private function ensureInitialized()
    {
        $elmPath = resource_path('elm');

        if (! is_dir($elmPath)) {
            mkdir($elmPath, 0755, true);
        }

        $laravelElmStuffPath = $elmPath . '/laravel-elm-stuff';
        if (! is_dir($laravelElmStuffPath)) {
            mkdir($laravelElmStuffPath, 0755, true);
        }

        $laravelElmSrcPath = $elmPath . '/src';
        if (! is_dir($laravelElmSrcPath)) {
            mkdir($laravelElmSrcPath, 0755, true);
        }

        $elmJsonPath = $elmPath . '/elm.json';
        if (! is_file($elmJsonPath)) {
            copy(__DIR__ . '/../Fixtures/elm.json', $elmJsonPath);
        }

        $laravelElmPath = $laravelElmStuffPath . '/LaravelElm.elm';
        if (! is_file($laravelElmPath)) {
            copy(__DIR__ . '/../Fixtures/LaravelElm.elm', $laravelElmPath);
        }

        return $elmPath;
    }
}
