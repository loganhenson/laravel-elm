<?php

namespace Tightenco\Elm;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Tightenco\Elm\Response render($component, $props = [])
 * @method static void share($key, $value = null)
 * @method static array getShared($key = null)
 * @method static array authRoutes()
 *
 * @see \Tightenco\Elm\ElmFacade
 */
class Elm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ElmFacade::class;
    }
}
