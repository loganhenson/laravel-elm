# Render your Elm programs in Laravel

So you want to render an elm program inside a laravel application?

This package makes it easy.

Recommended: The partner Javascript library with the same name, `npm i --dev laravel-elm`

## Installation

Begin by installing this package through Composer.

```js
{
    "require": {
		"tightenco/laravel-elm": "~1.0"
	}
}
```

And add the service provider to your application.

**config/app.php**
```
...
'providers' => [
    '...',
    Tightenco\Elm\ElmServiceProvider::class
];
...
```

When this provider is booted, you'll gain access to a helpful `Elm` facade, which you may use in your controllers.

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
