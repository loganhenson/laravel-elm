<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use LoganHenson\Elm\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';
}
