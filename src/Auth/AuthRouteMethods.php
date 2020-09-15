<?php

namespace Tightenco\Elm\Auth;

class AuthRouteMethods
{
    public function auth()
    {
        return function () {
            $namespace = class_exists($this->prependGroupNamespace('Auth\LoginController')) ? null : 'App\Http\Controllers';

            $this->group(['namespace' => $namespace], function () {
                $this->get('login', 'Auth\LoginController@showLoginForm')->middleware('guest')->name('login');
                $this->post('login', 'Auth\LoginController@login');

                // Logout Routes...
                $this->post('logout', 'Auth\LoginController@logout')->name('logout');

                // Registration Routes...
                $this->get('register', 'Auth\RegisterController@showRegistrationForm')->middleware('guest')->name('register');
                $this->post('register', 'Auth\RegisterController@register');

                // Password Reset Routes...
                $this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->middleware('guest')->name('password.request');
                $this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->middleware('guest')->name('password.email');
                $this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->middleware('guest')->name('password.reset');
                $this->post('password/reset', 'Auth\ResetPasswordController@reset')->middleware('guest')->name('password.update');

                // Email Verification Routes...
                $this->get('email/verify', 'Auth\VerificationController@show')->middleware('auth')->name('verification.notice');
                $this->get('email/verify/{id}/{hash}', 'Auth\VerificationController@verify')->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
                $this->post('email/resend', 'Auth\VerificationController@resend')->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
            });
        };
    }
}
