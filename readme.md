![Laravel Elm logo](https://raw.githubusercontent.com/tightenco/laravel-elm/master/laravel-elm-banner.png)

# A Platform for Elm on Laravel 

Tired of the paradox of choice and constant churn on the frontend?

Want a stable and opinionated platform to build on?

This package makes it seamless.

**Required**: The partner Javascript library with the same name, `npm i laravel-elm --save-dev`
> https://github.com/loganhenson/laravel-elm

## Add the elm runner to your `webpack.mix.js` e.g.:
> Production Example
```
const mix = require("laravel-mix");
const elm = require("laravel-elm");

mix.extend("elm", elm);

mix.js("resources/js/app.js", "public/js")
    .elm()
    .postCss("resources/css/main.css", "public/css", [require("tailwindcss")]);

if (mix.inProduction()) {
    mix.minify("public/js/elm.js").version(["public/js/elm.min.js"]);
}
```

## Installation

```
composer require tightenco/laravel-elm
```

## Create your first Elm Page
```
php artisan elm:create Example
```

## Watch your elm files just like you would everything else
```
npm run watch
```

You may then use the `Elm` facade to render your Elm Pages.

```php
use Tightenco\Elm\Elm;
...
public function index()
{
    return Elm::render('Example');
}
```

And then render it in your `app.blade.php` inside your `<body>`:

```blade
...
<head>
    ...
    <link href="{{ mix('/css/main.css') }}" rel="stylesheet">
</head>
<body>
@elm  {{-- This includes the appropriate elm.js script tag --}}
<script src="{{ mix('/js/app.js') }}"></script>
</body>
...
```

> Hello, Example!

## You can even pass props to your Elm Pages

```php
use Tightenco\Elm\Elm;
...
public function index()
{
    return Elm::render('Example', [
            'value' => 'Hello, World!',
            // You can pass anything you might need:
             'user' => auth()->user(),
        ]),
    ]);
}
```

## Or share values with all your Elm Pages

`AppServiceProvider.php`
```php
use Tightenco\Elm\Elm;
...
    public function boot()
    {
        ...

        Elm::share('user', function () {
            return auth()->user() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
            ] : null;
        });
    }
...
```

## Updating Assets
> Elm uses a service worker to ensure the latest assets are used. Add the `php artisan elm:pwa` to your "prod" command to ensure it gets the latest versions of you assets.
```json
{
  "scripts": {
    ...,
    "prod": "npm run production;php artisan elm:pwa",
    ...,
  }
}
```

## Testing

Just add this to your tests/TestCase.php setUp method.
```php
$this->withHeaders(['X-Laravel-Elm' => true]);
```

Now you can test everything via normal Laravel json assertion methods!
```php
$this->get(route('entries.index'))->assertJsonCount(1, 'props.entries');
```

## License

[View the license](https://github.com/tightenco/laravel-elm/blob/master/LICENSE) for this repo.
