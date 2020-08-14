<?php

namespace Tightenco\Elm;

use Illuminate\Support\Arr;

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
