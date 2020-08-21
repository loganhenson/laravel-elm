<?php

namespace Tightenco\Elm\Concerns;

use Illuminate\Support\Facades\File;

trait EnsureElmInitialized
{
    private function ensureInitialized()
    {
        $elmPath = resource_path('elm');

        if (! File::isDirectory($elmPath)) {
            File::makeDirectory($elmPath);
        }

        $laravelElmStuffPath = $elmPath . '/laravel-elm-stuff';
        if (! File::isDirectory($laravelElmStuffPath)) {
            File::makeDirectory($laravelElmStuffPath);
        }

        $elmJsonPath = $elmPath . '/elm.json';
        if (! File::isFile($elmJsonPath)) {
            File::copy(__DIR__ . '/../Fixtures/elm.json', $elmJsonPath);
        }

        $laravelElmPath = $laravelElmStuffPath . '/LaravelElm.elm';
        if (! File::isFile($elmJsonPath)) {
            File::copy(__DIR__ . '/../Fixtures/LaravelElm.elm', $laravelElmPath);
        }

        return $elmPath;
    }
}
