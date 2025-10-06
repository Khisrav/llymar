<?php

use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ConsultationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

/*
 * PORTFOLIO ROUTES
 */
Route::get('/portfolio/latest', [PortfolioController::class, 'getLatest'])
    ->middleware('throttle:60,1') // 60 requests per minute
    ->name('api.portfolio.latest');

Route::get('/portfolio', [PortfolioController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('api.portfolio.index');

Route::get('/portfolio/{portfolio}', [PortfolioController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('api.portfolio.show');

/*
 * LANDING PAGE OPTIONS ROUTES
 */
Route::get('/landing-page-options', function () {
    $options = \App\Models\LandingPageOption::orderBy('group')->orderBy('order')->get();
    return response()->json([
        'success' => true,
        'data' => $options,
    ]);
})->middleware('throttle:60,1')->name('api.landing_page_options');

/*
 * CONSULTATION ROUTES
 */
Route::post('/consultation-request', [ConsultationController::class, 'store'])
    ->middleware('throttle:10,1') // 10 requests per minute
    ->name('api.consultation.store');

/*
 *  WEBHOOK ROUTES
 */
Route::post('/webhook/tochka', function (Request $request) {
    $tochkaService = new \App\Services\TochkaBankService();
    $tochkaService->receiveWebhook($request);
    return response()->json(['status' => 'ok']);
})->name('webhook.tochka');
