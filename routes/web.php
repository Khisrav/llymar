<?php

use App\Http\Controllers\AppCalculatorController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Auth;

/*
 *  PUBLIC ROUTES
 */
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('auth'),
    ]);
});

Route::get('/auth', function() {
    if (Auth::check()) {
        return redirect()->route('app.home');
    }
    return Inertia::render('Auth/Index', [
        'canLogin' => Route::has('auth'),
    ]);
})->name('auth');

/*
 *  PRIVATE ROUTES
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/app', function () {
        return Inertia::render('App/Index');
    })->name('app.home');

    Route::get('/app/calculator', [AppCalculatorController::class, 'index'])->name('app.calculator');

    Route::get('/app/history', function () {
        return Inertia::render('App/History', [

        ]);
    })->name('app.history');
});

/*
 *  SOCIALITE ROUTES
 */
Route::get('login/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('login/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');

require __DIR__.'/auth.php';
