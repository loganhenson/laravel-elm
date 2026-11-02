<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use LoganHenson\Elm\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    use VerifiesEmails;

    protected $redirectTo = '/home';
}
