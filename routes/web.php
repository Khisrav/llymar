<?php

use App\Http\Controllers\AppCalculatorController;
use App\Http\Controllers\AppCartController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

/*
 *  PUBLIC ROUTES
 */
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('auth'),
    ]);
});

Route::get('/orders/{orderId}/list-pdf', [OrderController::class, 'listPDF'])
    ->name('orders.list_pdf');
Route::post('/orders/list-pdf-from-calc', [OrderController::class, 'listFromCalcPDF'])
    ->name('orders.calc_list_pdf');
Route::post('/orders/commercial-offer', [OrderController::class, 'commercialOfferPDF'])
    ->name('orders.commercial_offer_pdf');
// Route::get('/orders/sketch', [OrderController::class, 'sketchPDF'])
//     ->name('orders.sketch_pdf');
// Route::get('/orders/sketch', function() {
//     return view('orders.sketch_pdf');
// })->name('orders.sketch_pdf');

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
    
    Route::get('/app/account/settings', [UserController::class, 'show'])->name('app.account.settings');
    Route::post('/app/account/settings', [UserController::class, 'update'])->name('app.account.settings.update');
    
    Route::get('/app/orders/sketcher/{order_id}', [OrderController::class, 'sketcherPage'])->name('app.sketcher');
    Route::post('/app/order/sketch/download', [OrderController::class, 'sketchPDF'])->name('app.sketch');
    Route::post('/app/order/sketch/save', [OrderController::class, 'saveSketch'])->name('app.save.sketch');
});

/*
 *  SOCIALITE ROUTES
 */
Route::get('/login/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('/login/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');
Route::get('/logout', [SocialiteController::class, 'logout'])->name('logout');

require __DIR__.'/auth.php';
