# Render your Elm programs from Laravel

So you want to render multiple elm programs inside a laravel application?

This package makes it seamless.

**Required**: The partner Javascript library with the same name, `npm i --dev laravel-elm`
> https://github.com/loganhenson/laravel-elm

## Add the elm runner to your `webpack.mix.js` e.g.:
```
const mix = require('laravel-mix');
const elm = require('laravel-elm');

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .then(elm);
```

## Installation

```
composer require tightenco/laravel-elm
```

## Create your first Elm application
```
php artisan elm:create Example
```

## Watch your elm files just like you would everything else
```
npm run watch
```

You may then use the `Elm` facade to view your Elm apps.

```php
use Tightenco\Elm\Elm;
...
public function index()
{
    return view('home', [
        'Example' => Elm::make('Example'),
    ]);
}
```

And then render it in your view:

```php
{!! $Example !!}
```

> Hello, World!

## You can even pass flags to your Elm application
> You can generate a program with flags via `php artisan elm:create Example --with-flags`

```php
use Tightenco\Elm\Elm;
...
public function index()
{
    return view('home', [
        'Example' => Elm::make('Example', [
            'value' => 'Hello, World!'
            // You can pass anything you might need:
            // 'csrfToken' => csrf_token(),
            // 'user' => auth()->user(),
        ]),
    ]);
}
```

## License

[View the license](https://github.com/tightenco/laravel-elm/blob/master/LICENSE) for this repo.
