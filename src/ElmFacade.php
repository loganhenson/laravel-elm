<?php

namespace Tightenco\Elm;

use Illuminate\Support\Facades\Facade;

class ElmFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Elm';
    }
}
