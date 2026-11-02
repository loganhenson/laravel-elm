import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import laravelElm from 'laravel-elm';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/elm.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
        laravelElm({
            debug: true,
        }),
    ],
});
