<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Tightenco\Elm\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';
}
