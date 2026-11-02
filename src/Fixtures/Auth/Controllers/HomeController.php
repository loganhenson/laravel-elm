<?php

namespace App\Http\Controllers;

use LoganHenson\Elm\Elm;

class HomeController extends Controller
{
    public function index()
    {
        return Elm::render('Home');
    }
}
