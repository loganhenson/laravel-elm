![Laravel Elm logo](https://raw.githubusercontent.com/tightenco/laravel-elm/master/laravel-elm-banner.png)

# A Platform for Elm on Laravel 

Tired of the paradox of choice and constant churn on the frontend?

Want a stable and opinionated platform to build on?

This package makes it seamless.

## Installation

```
composer require tightenco/laravel-elm
php artisan elm:install
```
> Optional Auth Scaffolding (Tailwind)
```
php artisan elm:auth
```
> Then add `Elm::authRoutes()` to your `web.php`

## Create Your Own Elm Pages
```
php artisan elm:create Example
```

## Watch your elm files just like you would everything else
```
npm run watch
```

You use the `Elm` facade to render your Elm Pages.

```php
use Tightenco\Elm\Elm;
...
public function index()
{
    return Elm::render('Example');
}
```

It is magically rendered in your `app.blade.php`!
(`elm:install` sets up your `app.blade.php` so don't worry about adding the `@elm` directive manually if you don't want to.)
```blade
...
<head>
    ...
    <link href="{{ mix('/css/main.css') }}" rel="stylesheet">
</head>
<body>
@elm
<script src="{{ mix('/js/app.js') }}"></script>
</body>
...
```

> Hello, Example!

## You pass props to your Elm Pages

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
> Elm uses a service worker to ensure the latest assets are used. Add the `php artisan elm:sw` to your "prod" command to ensure it gets the latest versions of you assets.
```json
{
  "scripts": {
    ...,
    "prod": "npm run production;php artisan elm:sw",
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
