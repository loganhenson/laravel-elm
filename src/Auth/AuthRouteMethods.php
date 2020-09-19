<?php

namespace Tightenco\Elm\Auth;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

class AuthRouteMethods
{
    public function __invoke()
    {
        Route::get('login', [LoginController::class, 'showLoginForm'])->middleware('guest')->name('login');
        Route::post('login', [LoginController::class, 'login']);

        // Logout Routes...
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        // Registration Routes...
        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->middleware('guest')->name('register');
        Route::post('register', [RegisterController::class, 'register']);

        // Password Reset Routes...
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->middleware('guest')->name('password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('guest')->name('password.email');
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->middleware('guest')->name('password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->middleware('guest')->name('password.update');

        // Email Verification Routes...
        Route::get('email/verify', [VerificationController::class, 'show'])->middleware('auth')->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('email/resend', [VerificationController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
    }
}
