<?php

namespace Tightenco\Elm;

use Illuminate\Support\Facades\Facade;

/**
 * Class ElmFacade
 * @package Tightenco\Elm
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
