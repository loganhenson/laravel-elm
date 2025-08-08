<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="theme-color" content="#2196f3">
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >
        <link rel="preconnect" href="https://storage.googleapis.com">
        <link
            rel="shortcut icon"
            href="/favicon.ico"
            type="image/x-icon"
        >
        <link
            rel="icon"
            href="/favicon.ico"
            type="image/x-icon"
        >
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/elm.js'])
    </head>

    <body class="bg-white max-w-screen-lg mx-auto">
        @elm
    </body>
</html>
