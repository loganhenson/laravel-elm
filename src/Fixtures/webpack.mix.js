const mix = require("laravel-mix");
require("laravel-elm");

mix.js("resources/js/app.js", "public/js")
  .elm()
  .postCss("resources/css/app.css", "public/css", [require("tailwindcss")]);
