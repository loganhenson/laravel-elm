<?php

namespace App\Http\Controllers;

use Tightenco\Elm\Elm;

class HomeController extends Controller
{
    public function index()
    {
        return Elm::render('Home');
    }
}
