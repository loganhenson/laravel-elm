# Render your Elm programs in Laravel

So you want to render an elm program inside a laravel application?

This package makes it easy.

Recommended: The partner Javascript library with the same name, `npm i --dev laravel-elm`
> https://github.com/loganhenson/laravel-elm

## Installation

Begin by installing this package through Composer.

```js
{
    "require": {
        "tightenco/laravel-elm": "~2.0"
    }
}
```

You may then use the `Elm` facade to hydrate your Elm apps.

```php
use Tightenco\Elm\Elm;
...
public function index()
{
    return view('home', [
        'Example' => Elm::make('Example', [
            'name' => Auth::user()->name
        ])
    ]);
}
```

And in the view:

```php
{!! $Example !!}
```

## License

[View the license](https://github.com/tightenco/laravel-elm/blob/master/LICENSE) for this repo.
