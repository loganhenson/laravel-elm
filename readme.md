![Laravel Elm logo](https://raw.githubusercontent.com/tightenco/laravel-elm/master/laravel-elm-banner.png)

# A Platform for Elm on Laravel

Tired of the paradox of choice and constant churn on the frontend?

Want a stable and opinionated platform to build on?

This package makes it seamless.

## Requirements
- Laravel 8

## Docs

- [Installation](#installation)
- [Creating a page](#Creating-a-page)
- [Pass values to your page](#Pass-values-to-your-page)
- [Or share values with all your pages](#Or-share-values-with-all-your-pages)
- [Interop with Javascript](#Interop-with-Javascript)
- [Debugging](#Debugging)
  * [Laravel errors](#Laravel-errors)
  * [Devtools](#Devtools) (Coming soon!)
- [Deploying](#Deploying)
  * [Updating Assets](#Updating-assets)
- [Configuration](#Configuration)
  * [Hot reloading](#Hot-reloading)
- [Testing](#Testing)
  * [Laravel tests](#Laravel-tests)


## Installation
- Ensure you have an 8.x Laravel app ready (https://laravel.com/docs/8.x/installation)
- Now add the laravel-elm package
```
composer require tightenco/laravel-elm
```
- Run the elm:install command, this will:
  - add the npm companion package for Laravel Elm
  - setup your webpack.mix.js for Laravel Elm
  - setup your tailwind.config.js for Laravel Elm
```
php artisan elm:install
```
- Install the new npm dependencies
```
npm install
```
> Optional Auth Scaffolding (Tailwind)
- Run the elm:auth command, this will:
  - add all the routes & Elm pages for basic login/registration
  - add `Elm::authRoutes()` to your `web.php`
  - setup `app.blade.php` with the js script includes
```
php artisan elm:auth
```
> Note: Don't forget to run `php artisan migrate`!

### Watch your elm files just like you would everything else
> Note: Elm compilation will be drastically faster than you are used to 🔥
```
npm run watch
```
> And open your local site! (`valet link && valet open`)
> Try going to `/login` or `/register`!

## Creating a page
```
php artisan elm:create Welcome
```
> _this creates `resources/elm/Example/Welcome.elm`_

Now use the `Elm` facade to render your Elm Page!

> `routes/web.php`
```php
Route::get('/', function () {
    return Elm::render('Welcome');
});
```

> Hello, Example!

# Some Elm knowledge required from here on!
> Learning resources
- https://guide.elm-lang.org/

## Pass values to your page

> Update your Laravel route:
>
> `routes/web.php`
```php
Route::get('/', function () {
    return Elm::render('Welcome', ['name' => 'John']);
});
```
> Update your Elm page:
>
> `resources/elm/pages/Welcome.elm`
- add imports for decoding to the top
- add the `name` field to `Props` (`String`)
- update `decodeProps` with the `name` field
- use `props.name` in your `view` function

```elm
module Welcome exposing (..)

import Html exposing (Html, div, text)
import Html.Attributes exposing (class)
import Json.Decode exposing (Decoder, Value, decodeValue, string, succeed)
import Json.Decode.Pipeline exposing (required)
import LaravelElm exposing (Page, page)


type alias Props =
    { name : String }


type alias State =
    {}


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | NoOp


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "name" string


stateFromProps : Props -> State
stateFromProps props =
    {}


main : Page Model Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , update = update
        , view = view
        , newPropsMsg = NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props (decodeValue decodeProps newProps)
              , state = state
              }
            , Cmd.none
            )

        NoOp ->
            ( { props = props
              , state = state
              }
            , Cmd.none
            )


view : Model -> Html Msg
view { props, state } =
    div [ class "container mx-auto m-4 p-4" ]
        [ text <| "Hello, " ++ props.name ++ "!" ]

```

## Share values with all your pages

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

## Interop with Javascript
> Talk back and forth from JS & Elm
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

### Laravel errors
> Laravel errors are displayed in a modal on the frontend during development, using the same ignition error page that you are used to!

### DevTools
> Coming soon!

### Deploying

## Updating assets
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

### Hot reloading
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

### Laravel tests

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
