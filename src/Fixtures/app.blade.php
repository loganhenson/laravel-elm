<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#2196f3">
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <title>{{ config('app.name', 'Laravel') }}</title>
        {{-- VITE --}}
    </head>

    <body class="bg-white max-w-screen-lg mx-auto">
        {{-- @elm loads resources/js/elm.js via Vite and boots the current Elm page. --}}
        @elm
    </body>
</html>
