<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Tightenco\Elm\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
}
