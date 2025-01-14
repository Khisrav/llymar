<?php

use App\Http\Controllers\AppCalculatorController;
use App\Http\Controllers\AppCartController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Auth;

/*
 *  PUBLIC ROUTES
 */
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('auth'),
    ]);
});

Route::get('/orders/{orderId}/pdf', [OrderController::class, 'generateOrderPDF'])
    ->name('orders.pdf');

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

    Route::get('/app/history', [OrderController::class, 'index'])->name('app.history');
    
    Route::get('/app/cart', [AppCartController::class, 'index'])->name('app.cart');
    
    Route::post('/app/checkout', [OrderController::class, 'store'])->name('app.checkout');
    
    Route::get('/app/account/settings', function () {
        return Inertia::render('App/Account/Settings', [
            'user' => Auth::user(),
        ]);
    })->name('app.account.settings');
});

/*
 *  SOCIALITE ROUTES
 */
Route::get('login/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('login/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');

require __DIR__.'/auth.php';
