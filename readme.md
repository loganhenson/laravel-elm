# Render your Elm programs in Laravel

So you want to render an elm program inside a laravel application?

This package makes it easy.

## Installation

Begin by installing this package through Composer.

```js
{
    "require": {
		"tightenco/elm": "~1.0"
	}
}
```

When this provider is booted, you'll gain access to a helpful `Elm` facade, which you may use in your controllers.

```php
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
