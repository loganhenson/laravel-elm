<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Tightenco\Elm\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/home';
}
