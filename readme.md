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
        "tightenco/laravel-elm": "~1.0"
    }
}
```

And add the service provider and facade alias to your application config.

**config/app.php**
```
...
'providers' => [
    '...',
    Tightenco\Elm\ElmServiceProvider::class
];
...
```

```
...
'aliases' => [
    '...',
    'Elm' => Tightenco\Elm\ElmFacade::class,
];
```

You may then use the helpful `Elm` facade in your controllers.

```php
use Elm;
...
public function index()
{
    return view('home', [
        'example' => Elm::make('example', [
            'name' => Auth::user()->name
        ])
    ]);
}
```

## License

[View the license](https://github.com/tightenco/laravel-elm/blob/master/LICENSE) for this repo.
