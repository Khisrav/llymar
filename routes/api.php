<?php

use App\Http\Controllers\PortfolioController;
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
 *  WEBHOOK ROUTES
 */
Route::post('/webhook/tochka', function (Request $request) {
    $tochkaService = new \App\Services\TochkaBankService();
    $tochkaService->receiveWebhook($request);
    return response()->json(['status' => 'ok']);
})->name('webhook.tochka');
