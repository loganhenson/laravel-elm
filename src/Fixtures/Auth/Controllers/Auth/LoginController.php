<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Tightenco\Elm\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';
}
