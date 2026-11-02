<?php

use LoganHenson\Elm\Elm;

if (! function_exists('elm')) {
    function elm($page, $props = [])
    {
        return Elm::render($page, $props);
    }
}
