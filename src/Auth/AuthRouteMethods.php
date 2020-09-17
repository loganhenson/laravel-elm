<?php

namespace Tightenco\Elm\Auth;

use Illuminate\Support\Facades\Route;

class AuthRouteMethods
{
    public function __invoke()
    {
        Route::get('login', 'Auth\LoginController@showLoginForm')->middleware('guest')->name('login');
        Route::post('login', 'Auth\LoginController@login');

        // Logout Routes...
        Route::post('logout', 'Auth\LoginController@logout')->name('logout');

        // Registration Routes...
        Route::get('register', 'Auth\RegisterController@showRegistrationForm')->middleware('guest')->name('register');
        Route::post('register', 'Auth\RegisterController@register');

        // Password Reset Routes...
        Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->middleware('guest')->name('password.request');
        Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->middleware('guest')->name('password.email');
        Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->middleware('guest')->name('password.reset');
        Route::post('password/reset', 'Auth\ResetPasswordController@reset')->middleware('guest')->name('password.update');

        // Email Verification Routes...
        Route::get('email/verify', 'Auth\VerificationController@show')->middleware('auth')->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', 'Auth\VerificationController@verify')->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('email/resend', 'Auth\VerificationController@resend')->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
    }
}
