<?php

namespace Tighten\Elm;

use Illuminate\Support\Facades\Facade;

/**
 * Class ElmFacade
 * @package Laracasts\Utilities\JavaScript
 */
class ElmFacade extends Facade
{
    /**
     * The name of the binding in the IoC container.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Elm';
    }
}
