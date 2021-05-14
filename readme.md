![Laravel Elm logo](https://raw.githubusercontent.com/tightenco/laravel-elm/master/laravel-elm-banner.png)

# A Platform for Elm on Laravel 

Tired of the paradox of choice and constant churn on the frontend?

Want a stable and opinionated platform to build on?

This package makes it seamless.

## Requirements
- Laravel 8

## Installation
```
composer require tightenco/laravel-elm
php artisan elm:install
npm install
```
> Optional Auth Scaffolding (Tailwind)
```
php artisan elm:auth
```
> Then add `Elm::authRoutes()` to your `web.php`
> Note: Make sure you have run `php artisan migrate`, as this auth scaffold utilizes the default Laravel 8 `users` & `password_resets` tables.

## Watch your elm files just like you would everything else
> Note: Elm compilation will be drastically faster than you are used to 🔥
```
npm run watch
```

## Create Your Own Elm Pages
```
php artisan elm:create Example
```
> _creates `resources/elm/Example/Main.elm`_


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
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
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

## Ports
> Talk back and forth from JS & Elm easily
`resources/elm/ExamplePage.elm`
```elm
port module ExamplePage exposing (..)

port saveEmail : String -> Cmd msg

port receiveEmail : (Value -> msg) -> Sub msg
...
```
```js
LaravelElm.register("ExamplePage", page => {
    page.send("receiveEmail", localStorage.getItem("email"))

    page.subscribe("saveEmail", email => {
        localStorage.setItem("email", email);
    });
});
```

## Debugging
Install the laravel-elm-devtools extension for chrome

## Updating Assets
> Laravel Elm uses a service worker to ensure the latest assets are used in production. Add the `php artisan elm:sw` to your "prod" command to ensure it gets the latest versions of you assets.
```json
{
  "scripts": {
    ...,
    "prod": "npm run production;php artisan elm:sw",
    ...,
  }
}
```

## Configuration
> You may want to disable hot reloading & debugging in development if your app is _extremely_ large / complex
- Create an `elm.php` Laravel config file and set `debug` to `false`
- Then in `webpack.mix.js` add
```
...
    .elm({debug: false})
...
```
> This disables the generation of debug code & does not start the hot reload server during `npm run watch`

## Testing

Add this to your tests/TestCase.php setUp method.
```php
$this->withHeaders(['X-Laravel-Elm' => 'true']);
```

Now you can test everything via normal Laravel json assertion methods!
```php
$this->get(route('entries.index'))->assertJsonCount(1, 'props.entries');
```

## License

[View the license](https://github.com/tightenco/laravel-elm/blob/master/LICENSE) for this repo.
