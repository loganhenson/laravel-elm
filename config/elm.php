<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Root view
    |--------------------------------------------------------------------------
    |
    | The Blade view every Elm page is rendered into. It must contain the
    | `@elm` directive. Defaults to `app` (resources/views/app.blade.php);
    | `php artisan elm:install` switches this to `elm` when your app already
    | uses `app.blade.php` for something else (e.g. an Inertia/React root).
    |
    */

    'root_view' => env('ELM_ROOT_VIEW', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    |
    | Enables the error modal, dd() modal, devtools messages and (while the
    | Vite dev server is running) hot reloading. `null` follows APP_DEBUG.
    |
    */

    'debug' => env('ELM_DEBUG'),

];
