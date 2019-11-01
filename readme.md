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
php artisan elm:create Hello
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
        'Hello' => Elm::make('Hello'),
    ]);
}
```

And then render it in your view:

```php
{!! $Hello !!}
```

> Hello, World!

## You can even pass flags to your Elm application

```php
use Tightenco\Elm\Elm;
...
public function index()
{
    return view('home', [
        'Hello' => Elm::make('Hello', [
            'csrfToken' => csrf_token(),
            'user' => auth()->user(),
        ]),
    ]);
}
```

## License

[View the license](https://github.com/tightenco/laravel-elm/blob/master/LICENSE) for this repo.
