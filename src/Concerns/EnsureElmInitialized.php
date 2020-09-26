<?php

namespace Tightenco\Elm\Concerns;

trait EnsureElmInitialized
{
    private function ensureInitialized($installing = false)
    {
        $elmPath = resource_path('elm');

        if (! is_dir($elmPath)) {
            mkdir($elmPath, 0755, true);
        }

        $elmGitIgnorePath = $elmPath . '/.gitignore';
        if (! is_file($elmGitIgnorePath)) {
            file_put_contents($elmGitIgnorePath, 'elm-stuff');
        }

        $laravelElmStuffPath = $elmPath . '/laravel-elm-stuff';
        if (! is_dir($laravelElmStuffPath)) {
            mkdir($laravelElmStuffPath, 0755, true);
        }

        $laravelElmPagesPath = $elmPath . '/pages';
        if (! is_dir($laravelElmPagesPath)) {
            mkdir($laravelElmPagesPath, 0755, true);
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
        if (! is_file($laravelElmPath) || $installing) {
            copy(__DIR__ . '/../Fixtures/LaravelElm.elm', $laravelElmPath);
        }

        return $elmPath;
    }
}
