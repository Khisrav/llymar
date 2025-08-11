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
