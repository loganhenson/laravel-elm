<?php

namespace Tightenco\Elm;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;

class ElmFacade
{
    protected $sharedProps = [];

    public function share($key, $value = null)
    {
        if (is_array($key)) {
            $this->sharedProps = array_merge($this->sharedProps, $key);
        } else {
            Arr::set($this->sharedProps, $key, $value);
        }
    }

    public function getShared($key = null)
    {
        if ($key) {
            return Arr::get($this->sharedProps, $key);
        }

        return $this->sharedProps;
    }

    public function render(string $component, $flags = [])
    {
        return new Response(
            $component,
            array_merge($this->sharedProps, $flags),
        );
    }
}
