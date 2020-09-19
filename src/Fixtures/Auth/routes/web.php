<?php

Elm::authRoutes();

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
});
