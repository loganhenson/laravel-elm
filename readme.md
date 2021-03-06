![Laravel Elm logo](https://raw.githubusercontent.com/tightenco/laravel-elm/master/laravel-elm-banner.png)

<hr>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tightenco/laravel-elm.svg?style=flat-square)](https://packagist.org/packages/tightenco/laravel-elm)

# A Platform for Elm on Laravel

Tired of the paradox of choice and constant churn on the frontend?

Want a stable and opinionated platform to build on?

This package makes it seamless.

## Requirements

-   Laravel 8

## Docs

-   [Installation](#installation)
-   [Creating a page](#Creating-a-page)

> Some Elm knowledge required from here on!
>
> [Elm learning resources](#Some-Elm-knowledge-required-from-here-on)

-   [Pass values to your page](#Pass-values-to-your-page)
-   [Share values with all your pages](#Share-values-with-all-your-pages)
-   [Routing](#Routing)
-   [Validation errors](#Validation-errors)
-   [Interop with Javascript](#Interop-with-Javascript)
-   [Persistent scroll](#Persistent-scroll)
-   [Progress indicators](#Progress-indicators)
-   [Debugging](#Debugging)
    -   [Laravel errors](#Laravel-errors)
    -   [Devtools](#Devtools) (Coming soon!)
-   [Deploying](#Deploying)
    -   [Updating Assets](#Updating-assets)
-   [Configuration](#Configuration)
    -   [Hot reloading](#Hot-reloading)
-   [Testing](#Testing)
    -   [Laravel tests](#Laravel-tests)
-   [Example apps](#Example-apps)

## Installation

-   Ensure you have an 8.x Laravel app ready (https://laravel.com/docs/8.x/installation)
-   Now add the laravel-elm package

```
composer require tightenco/laravel-elm
```

-   Run the `elm:install` command, this will:
    -   add the npm companion package for Laravel Elm
    -   setup your `webpack.mix.js` for Laravel Elm
    -   setup your `tailwind.config.js` for Laravel Elm

```
php artisan elm:install
```

-   Install the new npm dependencies

```
npm install
```

> Optional Auth Scaffolding (Tailwind)

-   Run the elm:auth command, this will:
    -   add all the routes & Elm pages for basic login/registration
    -   add `Elm::authRoutes()` to your `web.php`
    -   setup `app.blade.php` with the js script includes

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

> General assets note!
>
> You can add `public/js` and `public/css` to your `.gitignore` if you wish to avoid committing these built files!

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

# Some Elm knowledge required from here on

> Learning resources

-   https://guide.elm-lang.org/

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

-   add imports for decoding to the top
-   add the `name` field to `Props` (`String`)
-   update `decodeProps` with the `name` field
-   use `props.name` in your `view` function

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

## Routing

Routing in Laravel Elm is handled completely by your Laravel routes!

However, we can _use_ those routes in our Elm code in a built in way.

1. Add a route, for example, our Welcome page, with a `name`:

```php
Route::get('/', function () {
    return Elm::render('Welcome');
})->name('welcome');
```

2. Run the `elm:routes` command to generate the Elm routes file
    > `resources/elm/laravel-elm-stuff/Routes.elm` (don't edit this manually)

```bash
php artisan elm:routes
```

3. Now we can send users to this page from Elm in our `update` handlers:
    > Send the user to `/`

```elm
Routes.get Routes.welcome
```

> Or even post some data to an endpoint:
>
> `POST /todos` with the `"description"` of `"add more docs"`

```elm
Routes.post <|
  Json.Encode.object
      [ ( "url", Json.Encode.string <| Routes.todosStore )
      , ( "data"
        , Json.Encode.object
              [ ( "description", Json.Encode.string "add more docs" )
              ]
        )
      ]
```

## Validation errors

> The `errors` value is automatically passed to your Elm views, all you need to do is add it to your props to use it!

```elm
import LaravelElm exposing (Errors)

type alias Props =
    { errors : Errors }

decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))
```

## Interop with Javascript

> Talk back and forth from JS & Elm
> `resources/elm/ExamplePage.elm`

```elm
port module ExamplePage exposing (..)

port saveEmail : String -> Cmd msg

port receiveEmail : (Value -> msg) -> Sub msg
...
```

```js
LaravelElm.register("ExamplePage", (page) => {
    page.send("receiveEmail", localStorage.getItem("email"));

    page.subscribe("saveEmail", (email) => {
        localStorage.setItem("email", email);
    });
});
```

## Debugging

### Laravel errors

> Laravel errors are displayed in a modal on the frontend during development, using the same ignition error page that you are used to!

### DevTools

> Coming soon!

## Persistent scroll

> Sometimes you want an "app like" preservation of scroll positions while navigating to and from different pages.

Laravel Elm has built in support for this, by saving the viewport values into the history.

To use it you need to:

-   Import the components we need

```elm
import LaravelElm exposing (Scroll, Viewports, decodeViewports, preserveScroll, receiveNewProps, saveScroll, setViewports)
```

-   Add a `SaveScroll` msg

```elm
type Msg
    = NewProps Value
    | NoOp
    | SaveScroll Scroll
```

-   Add the `viewports` prop

```elm
type alias Props =
    { errors : Errors
    , loading : Bool
    , viewports : Viewports }
```

-   Add the decoder for the `viewports` prop

```elm
decodeProps : Decoder Props
decodeProps =
    Json.Decode.succeed Props
        |> required "viewports" decodeViewports
```

-   Make sure we are using the saved viewport positions on mount

```elm
main : Program Value (Result Error Model) Msg
main =
    LaravelElm.pageWithMountAndSubscriptions
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , view = view
        , update = update
        , subscriptions = \_ -> receiveNewProps NewProps
        , onMount = \props -> setViewports NoOp props.viewports
        }
```

-   Make sure we are using the saved viewport positions on update
-   As well as saving the viewport positions on scroll

```elm
update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            case decodeValue decodeProps newProps of
                Ok decodedProps ->
                    ( { props = decodedProps
                      , state = state
                      }
                    , setViewports NoOp decodedProps.viewports
                    )

                Err _ ->
                    ( { props = props, state = state }, Cmd.none )

        SaveScroll scroll ->
            ( { props = props, state = state }, saveScroll scroll )
```

-   Finally, use `preserveScroll` on our html element ("key" should be unique for multiple scroll containers)

```elm
view : Model -> Html Msg
view { props, state } =
    div
        ([ class "h-full overflow-y-scroll" ]
            ++ preserveScroll SaveScroll "key"
        )
        [ text "long content" ]
```

## Progress indicators

### In Elm

> The `loading` prop is automatically passed to all your Elm views, you only
> need to add it to your props to use it!

```elm
type alias Props =
    { loading : Bool }

decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "loading" bool
```

### In Javascript

> You can access the loading state in javascript via the `elm-loading` event
>
> Example using nprogress to show a top progress bar:
>
> (after 180ms, so it does not appear for fast connections)

```js
import Nprogress from "nprogress";

let loadingTimeout = null;
Nprogress.configure({ showSpinner: false, minimum: 0.4 });
window.addEventListener("elm-loading", function ({ detail: loading }) {
    clearTimeout(loadingTimeout);

    if (loading) {
        loadingTimeout = setTimeout(Nprogress.start, 180);
    } else {
        Nprogress.done();
    }
});
```

## Deploying

### Updating assets

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

-   Create an `elm.php` Laravel config file and set `debug` to `false`
-   Then in `webpack.mix.js` add

```
...
    .elm({debug: false})
...
```

> This disables the generation of debug code & does not start the hot reload server during `npm run watch`

## Testing

### Laravel tests

All your normal http tests function identically to how they do in a vanilla Laravel app.

But if we want to assert against the props that are sent to Elm, we can add the `X-Laravel-Elm` header to our `tests/TestCase.php` `setUp` method:

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();
        $this->withHeaders(['X-Laravel-Elm' => 'true']);
    }
}
```

Now we can test everything via normal Laravel json assertion methods!

```php
$this->get(route('entries.index'))->assertJsonCount(1, 'props.entries');
```

## Example apps

-   [TodoMVC in Laravel Elm](https://github.com/loganhenson/laravel-elm-todomvc)

## Contributing

To get started contributing to Laravel Elm, check out [the contribution guide](CONTRIBUTING.md).

## Credits

-   [Logan Henson](https://twitter.com/logan_j_henson)
-   [All contributors](https://github.com/tighten/laravel-elm/contributors)

## Security

If you discover any security related issues, please email <hello@tighten.co> instead of using the issue tracker.

## License

[View the license](https://github.com/tightenco/laravel-elm/blob/master/LICENSE) for this repo.
